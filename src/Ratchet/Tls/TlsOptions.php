<?php

namespace Ratchet\Tls;

class TlsOptions
{
    const FIELD_CERTIFICATE_PATH = 'local_cert';
    const FIELD_CERTIFICATE_KEY = 'local_pk';
    const FIELD_ALLOW_SELF_SIGNED = 'allow_self_signed';
    const FIELD_VERIFY_PEER = 'verify_peer';
    const FIELD_VERIFY_PEER_NAME = 'verify_peer_name';

    /** @var string */
    private $certificatePath;

    /** @var string */
    private $certificateKey;

    /** @var bool */
    private $allowSelfSigned;

    /** @var bool */
    private $verifyPeer;

    /** @var bool */
    private $verifyPeerName;

    /**
     * TlsOptions constructor.
     *
     * @param string $certificatePath
     * @param string $certificateKey
     * @param bool   $allowSelfSigned
     * @param bool   $verifyPeer
     * @param bool   $verifyPeerName
     */
    public function __construct(
        $certificatePath,
        $certificateKey,
        $allowSelfSigned = false,
        $verifyPeer = true,
        $verifyPeerName = true
    ) {
        $this->certificatePath = $certificatePath;
        $this->certificateKey = $certificateKey;
        $this->allowSelfSigned = $allowSelfSigned;
        $this->verifyPeer = $verifyPeer;
        $this->verifyPeerName = $verifyPeerName;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::FIELD_CERTIFICATE_PATH => $this->certificatePath,
            self::FIELD_CERTIFICATE_KEY => $this->certificateKey,
            self::FIELD_ALLOW_SELF_SIGNED => $this->allowSelfSigned,
            self::FIELD_VERIFY_PEER => $this->verifyPeer,
            self::FIELD_VERIFY_PEER_NAME => $this->verifyPeerName,
        ];
    }
}