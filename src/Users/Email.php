<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Model\SettableModel;

class Email extends SettableModel
{
    /**
     * Set the type of this email address
     * Possible codes are listed in the 'UserEmailTypes' code table.
     * 
     * @param string $email_type The email type
     * @param string $description The description of this email type
     */
    public function setEmailType($email_type, $description = null)
    {
        $emailTypeObj = ['value' => $email_type];
        if ($description) {
            $emailTypeObj['desc'] = $description;
        }
        $this->data->email_type = [(object) $emailTypeObj];
    }
}
