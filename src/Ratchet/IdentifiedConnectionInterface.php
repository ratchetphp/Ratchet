<?php
namespace Ratchet;

/**
 * A proxy object representing a connection which can be identified by an id.
 */
interface IdentifiedConnectionInterface extends ConnectionInterface {

    /**
     * Unique identifier of a connection
     * @return mixed
     */
    function getId();
}
