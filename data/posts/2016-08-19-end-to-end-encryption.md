---
layout: post
title: End-to-end encryption with Zend Framework 3
date: 2016-08-19T08:45:00-05:00
author: Enrico Zimuel
url_author: http://www.zimuel.it
permalink: /blog/2016-08-19-end-to-end-encryption.html
categories:
- blog
- cryptography
- seurity

---

Recently, we released [zend-crypt](https://github.com/zendframework/zend-crypt)
3.1.0, the cryptographic component from [Zend Framework](https://framework.zend.com/).
This last version includes a [hybrid cryptosystem](https://docs.zendframework.com/zend-crypt/hybrid/),
a feature that can be used to implement [end-to-end encryption](https://en.wikipedia.org/wiki/End-to-end_encryption)
schema in PHP.

A hybrid cryptosystem is a cryptographic mechanism that uses symmetric encyption
(e.g. [AES](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)) to
encrypt a message, and public-key cryptography (e.g.
[RSA](https://en.wikipedia.org/wiki/RSA_%28cryptosystem%29)) to protect the
encryption key. This methodology guarantee two advantages: the speed of a
symmetric algorithm and the security of public-key cryptography.

Before we talk about the PHP implementation, let's explore the hybrid mechanism
in more detail. Below is a diagram demonstrating a hybrid encryption schema:

![Encryption schema](/img/blog/hybrid-encryption.png)

A user (the *sender*) wants to send a protected message to another user
(the *receiver*). He/she generates a **random session key** (one-time pad) and uses
this key with a symmetric algorithm to encrypt the message (in the figure, *Block
cipher* represents an [authenticated encryption](https://en.wikipedia.org/wiki/Authenticated_encryption)
algorithm). At the same time, the *sender* encrypts the session key using the
public key of the *receiver*. This operation is done using a public-key
algorithm, e.g., RSA. Once the encryption is done, the *sender* can send
the encrypted session key along with the encrypted message to the *receiver*.
The *receiver* can decrypt the session key using his/her private key, and
consequently decrypt the message.

This idea of combining together symmetric and asymmetric (public-key) encryption
can be used to implement end-to-end encryption (**E2EE**). An E2EE is a
communication system that encrypts messages exchanged by two users with the
property that only the two users can decrypt the message. End-to-end encryption
has become quite popular in the last years with the usage in popular software,
and particularly messaging systems, such as [WhatsApp](https://www.whatsapp.com/faq/en/general/28030015).
More generally, when you have a software used by many users, end-to-end
encryption can be used to protect information exchanged by users. Only the
users can access (decrypt) exchanged information; even the administrator of
the system is not able to access this data.

## Build end-to-end encryption in PHP

We want to implement end-to-end encryption for a web application with user
authentication. We will use zend-crypt 3.1.0 to implement our cryptographic
schemas. This component of Zend Framework uses the [OpenSSL extension](http://php.net/manual/en/book.openssl.php)
for PHP for its cryptographic primitives.

The first step is to create public and private keys for each users. Typically,
this step can be done when the user credentials are created. To generare the
pairs of keys, we can use `Zend\Crypt\PublicKey\RsaOptions`. Below
is an example demonstrating how to generate public and private keys to
store in the filesystem:

```php
use Zend\Crypt\PublicKey\RsaOptions;
use Zend\Crypt\BlockCipher;

$username = 'alice';
$password = 'test'; // user's password

// Generate public and private key
$rsaOptions = new RsaOptions();
$rsaOptions->generateKeys([
    'private_key_bits' => 2048
]);
$publicKey  = $rsaOptions->getPublicKey()->toString();
$privateKey = $rsaOptions->getPrivateKey()->toString();

// store the public key in a .pub file
file_put_contents($username . '.pub', $publicKey);

// encrypt and store the private key in a file
$blockCipher = BlockCipher::factory('openssl', array('algo' => 'aes'));
$blockCipher->setKey($password);
file_put_contents($username, $blockCipher->encrypt($privateKey));
```

In the above example, we generated a private key of 2048 bits. If you are wondering
why not 4096 bits, this is questionable and depends on the real use
case. For the majority of applications, 2048 is still a good key size, at least
until 2030. If you want more security and you don't care about the additional
CPU time, you can increase the key size to 4096. We suggest reading the following
blog posts for more information on key key size:

- [RSA Key Sizes: 2048 or 4096 bits?](https://danielpocock.com/rsa-key-sizes-2048-or-4096-bits)
- [The Big Debate, 2048 vs. 4096, Yubico’s Position](https://www.yubico.com/2015/02/big-debate-2048-4096-yubicos-stand/)
- [HTTPS Performance, 2048-bit vs 4096-bit](https://blog.nytsoi.net/2015/11/02/nginx-https-performance)

We did not generate the private key using a passphrase; this is because the
OpenSSL extension of PHP does not support **AEAD** (Authenticated Encrypt with
Associated Data) mode yet; AEAD mode will be supported starting in
[PHP 7.1](https://wiki.php.net/rfc/openssl_aead), which should release this
autumn.

The default passphrase encryption algorithm for OpenSSL is
[des-ede3-cbc](https://en.wikipedia.org/wiki/Triple_DES) using
[PBKDF2](https://en.wikipedia.org/wiki/PBKDF2) with 2048 iterations for
generating the encryption key from the user's password. Even if this encryption
algorithm is quite good, the number of iterations of PBKDF2 is not optimal;
zend-crypt improves on this in a variety of ways, out-of-the-box.  As
demonstrated above, we use `Zend\Crypt\BlockCipher` to encrypt the private key;
this class provides [encrypt-then-authenticate](http://www.daemonology.net/blog/2009-06-24-encrypt-then-mac.html)
using the **AES-256** algorithm for encryption and **HMAC-SHA-256** for
authentication. Moreover, `BlockCipher` uses the [PBKDF2](https://en.wikipedia.org/wiki/PBKDF2)
algorithm to derivate the encryption key from the user's key (password). The
default number of iterations for PBKDF2 is 5000, and you can increase it using
the `BlockCipher::setKeyIteration()` method.

In the example, we stored the public and private keys in two files named,
respectively, `$username.pub` and `$username`. Because the private file is encrypted,
using the user's password, it can be access only by the user. This is a very
important aspect for the security of the entire system (we take for granted that
the web application stores the hashes of the user's passwords using a secure
algorithm such as [bcrypt](https://en.wikipedia.org/wiki/Bcrypt)).

Once we have the public and private keys for the users, we can start using the
hybrid cryptosystem provided by zend-crypt. For instance, imagine *Alice*
wants to send an encrypted message to *Bob*:

```php
use Zend\Crypt\Hybrid;
use Zend\Crypt\BlockCipher;

$sender   = 'alice';
$receiver = 'bob';
$password = 'test'; // bob's password

$msg = sprintf('A secret message from %s!', $sender);

// encrypt the message using the public key of the receiver
$publicKey  = file_get_contents($receiver . '.pub');
$hybrid     = new Hybrid();
$ciphertext = $hybrid->encrypt($msg, $publicKey);

// send the ciphertext to the receiver

// decrypt the private key of bob
$blockCipher = BlockCipher::factory('openssl', ['algo' => 'aes']);
$blockCipher->setKey($password);
$privateKey = $blockCipher->decrypt(file_get_contents($receiver));

$plaintext = $hybrid->decrypt($ciphertext, $privateKey);

printf("%s\n", $msg === $plaintext ? "The message is: $msg" : 'Error!');
```

The above example demonstrates encrypting information between two users. Of
course, in this case, the sender (*Alice*) knows the message because she wrote
it. More in general, if we need to store a secret between multiple users, we need
to specify the public keys to be used for encryption.

The hybrid component of zend-crypt supports encrypting messages for multiple
recipients. To do so, pass an array of public keys in the `$publicKey` parameter
of `Zend\Crypt\Hybrid::encrypt($data, $publicKey)`.

Below demonstrates encrypting a file for two users, *Alice* and *Bob*.

```php
use Zend\Crypt\Hybrid;
use Zend\Crypt\BlockCipher;

$data    = file_get_contents('path/to/file/to/protect');
$pubKeys = [
  'alice' => file_get_contents('alice.pub'),
  'bob'   => file_get_contents('bob.pub')
];

$hybrid     = new Hybrid();

// Encrypt using the public keys of both alice and bob
$ciphertext = $hybrid->encrypt($data, $pubKeys);

file_put_contents('file.enc', $ciphertext);

$blockCipher = BlockCipher::factory('openssl', ['algo' => 'aes']);

$passwords = [
  'alice' => 'password of Alice',
  'bob'   => 'password of Bob'
];

// decrypt using the private keys of alice and bob, one at time
foreach ($passwords as $id => $pass) {
  $blockCipher->setKey($pass);
  $privateKey = $blockCipher->decrypt(file_get_contents($id));
  $plaintext  = $hybrid->decrypt($ciphertext, $privateKey, null, $id);
  printf("%s for %s\n", $data === $plaintext ? 'Decryption ok' : 'Error', $id);
}
```

For decryption, we used a hard coded password for the users. Usually, the user's
password is provided during the login process of a web application and should
not be stored as permanent data; for instance, the user's password can be saved
in a PHP session variable for temporary usage. If you use sessions to save
the user's password, ensure that data is protected; the
[PHP-Secure-Session](https://github.com/ezimuel/PHP-Secure-Session) library or
the [Suhosin](https://suhosin.org) PHP extension will help you do so.

To decrypt the file we used the `Zend\Crypt\Hybrid::decrypt()` function,
where we specified the `$privateKey`, a `null` passphrase, and finally the `$id`
of the privateKey. This parameters are necessary to find the correct key to use
in the header of the encrypted message.
