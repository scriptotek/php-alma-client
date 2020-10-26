<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Model\SettableModel;

class Phone extends SettableModel
{
    /**
     * Set the type of this phone number
     * Possible values are listed in the 'UserPhoneTypes' code table.
     *
     * @param string $phone_type The phone type
     * @param string $description The description of this phone type
     */
    public function setPhoneType($phone_type, $description = null)
    {
        $phoneTypeObj = ['value' => $phone_type];
        if ($description) {
            $phoneTypeObj['desc'] = $description;
        }
        $this->data->phone_type = [(object) $phoneTypeObj];
    }
}
