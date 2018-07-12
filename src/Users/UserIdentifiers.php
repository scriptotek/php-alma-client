<?php

namespace Scriptotek\Alma\Users;

class UserIdentifiers
{
    /* @var string */
    protected $id;

    /* @var \stdClass */
    protected $data;

    /**
     * UserIdentifiers constructor.
     *
     * @param \stdClass   $data
     */
    public function __construct($id, $data = null)
    {
        $this->id = $id;
        $this->data = $data ?? [];
    }

    /**
     * Get a flat array of all the user IDs.
     *
     * @param string $status (Default: 'ACTIVE').
     *
     * @return string[]
     */
    public function all($status='ACTIVE')
    {
        $ids = [$this->id];
        foreach ($this->data as $identifier) {
            if (is_null($status) || $identifier->status == $status) {
                $ids[] = $identifier->value;
            }
        }

        return $ids;
    }

    /**
     * Get all active user identifiers of a given type, like 'BARCODE' or 'UNIV_ID'.
     *
     * @param string $value
     *
     * @return null|string
     */
    public function allOfType($value, $status = 'ACTIVE')
    {
        $ids = [];
        foreach ($this->data as $identifier) {
            if ($identifier->id_type->value == $value && (is_null($status) || $identifier->status == $status)) {
                $ids[] = $identifier->value;
            }
        }
        return $ids;
    }

    /**
     * Get the first active user identifier of a given type, like 'BARCODE' or 'UNIV_ID'.
     *
     * @param string $value
     *
     * @return null|string
     */
    public function firstOfType($value, $status = 'ACTIVE')
    {
        foreach ($this->data as $identifier) {
            if ($identifier->id_type->value == $value && (is_null($status) || $identifier->status == $status)) {
                return $identifier->value;
            }
        }
    }

    /**
     * Get the first active barcode.
     *
     * @return null|string
     */
    public function getBarcode()
    {
        return $this->firstOfType('BARCODE');
    }

    /**
     * Get all active barcodes.
     *
     * @return string[]
     */
    public function getBarcodes()
    {
        return $this->allOfType('BARCODE');
    }

    /**
     * Get the first active university id.
     *
     * @return null|string
     */
    public function getUniversityId()
    {
        return $this->firstOfType('UNIV_ID');
    }

    /**
     * Get all active university ids.
     *
     * @return string[]
     */
    public function getUniversityIds()
    {
        return $this->allOfType('UNIV_ID');
    }

    public function __get($key)
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }
}
