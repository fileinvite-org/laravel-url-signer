<?php

return [

    /*
     * The private key used to create the signature & sign the url.
     */
    'private_key' => "-----BEGIN PRIVATE KEY-----
MIIBUwIBADANBgkqhkiG9w0BAQEFAASCAT0wggE5AgEAAkEAoD/OrbcvXPjnNh9w
3N0Iv2WMD8cCQuCiFJ5J0rqySNk9CLh95QBTW8U2n5gCpKbsnBDzYGqjXczgcpeS
oV1MBQIDAQABAkBKJRvIzrBQcS3xodyDpJWukRqcFjVaojr8FT0NDqr8/yMbc7nh
9Z+eLObtQKEsb7W9WJAIPtU9/Jb2DIsM7yXBAiEA08Xdf0jo4vFipIeuONLsXIKI
eviC0CoEsAM9SBwz5rUCIQDBt02qVOAV7DvYJ+guQiQKjGGmGRRlnvf0+lpokBFS
EQIgZYxh9CFAG2LojEDLmE/8KzgkveLgx3T+nnBPOH7dnGkCIDfDvtXTzRZChQ+6
nToDzEBFHS8ldRuz4YliMHh9UJ8hAiAH1SlMr07/sbgJvy+Q+sVcLU8UuVeNeEeB
NYgXJmMgWQ==
-----END PRIVATE KEY-----",

    /**
     * The public key used to verify the signed url signature.
     */
    'public_key' => "-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAKA/zq23L1z45zYfcNzdCL9ljA/HAkLg
ohSeSdK6skjZPQi4feUAU1vFNp+YAqSm7JwQ82Bqo13M4HKXkqFdTAUCAwEAAQ==
-----END PUBLIC KEY-----",

    /*
     * The default expiration time of a URL in minutes.
     */
    'default_expiration' => 60,

    /*
     * These strings are used a parameter names in a signed url.
     */
    'parameters' => [
        'expires'   => 'expires',
        'signature' => 'signature',
    ],

];
