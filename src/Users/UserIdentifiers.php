<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Model\Model;

/**
 * Collection of identifiers belonging to some User.
 */
class UserIdentifiers extends Model
{
    /**
     * Get a flat array of all the user IDs.
     *
     * @param string $status (Default: 'ACTIVE').
     * @return string[]
     */
    public function all($status='ACTIVE')
    {
        $ids = [$this->data->primary_id];
        foreach ($this->data->user_identifier as $identifier) {
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
     * @param string $status
     * @return array
     */
    public function allOfType($value, $status = 'ACTIVE')
    {
        $ids = [];
        foreach ($this->data->user_identifier as $identifier) {
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
     * @param string $status
     * @return null|string
     */
    public function firstOfType($value, $status = 'ACTIVE')
    {
        foreach ($this->data->user_identifier as $identifier) {
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
}
