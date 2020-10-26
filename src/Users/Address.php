<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Model\SettableModel;

class Address extends SettableModel
{
    /**
     * Set the type of this address
     * Possible values are listed in the 'UserAddressTypes' code table.
     *
     * @param string $address_type The address type
     * @param string $description The description of this address type
     */
    public function setAddressType($address_type, $description = null)
    {
        $addressTypeObj = ['value' => $address_type];
        if ($description) {
            $addressTypeObj['desc'] = $description;
        }
        $this->data->address_type = [(object) $addressTypeObj];
    }
}
