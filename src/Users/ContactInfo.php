<?php

namespace Scriptotek\Alma\Users;

use Scriptotek\Alma\Model\Model;

class ContactInfo extends Model
{
    
    /**
     * Get the user's preferred SMS number.
     *
     * @return Phone|null
     */
    public function getSmsNumber()
    {
        if ($this->data->phone) {
            foreach ($this->data->phone as $phone) {
                if ($phone->preferred_sms) {
                    return Phone::make($this->client, $phone);
                }
            }
        }
        return null;
    }
 
    /**
     * Remove the preferred SMS flag from any number.
     */
    public function unsetSmsNumber()
    {
        if ($this->data->phone) {
            foreach ($this->data->phone as $phone) {
                if ($phone->preferred_sms) {
                    $phone->preferred_sms = false;
                }
            }
        }
    }

    /**
     * Set the user's preferred SMS number, creating a new internal mobile number if needed
     * @param $number string The SMS-capable mobile phone number
     */
    public function setSmsNumber($number)
    {
        $currentNumber = $this->getSmsNumber();
        if ($currentNumber && $number === $currentNumber->phone_number) {
            return;
        }
        $this->unsetSmsNumber();
        if ($this->data->phone) {
            foreach ($this->data->phone as $phone) {
                if ($phone->phone_number === $number) {
                    $phone->preferred_sms = true;
                    return;
                }
            }
        }
        $this->addSmsNumber($number);
    }

    /**
     * Add the user's preferred SMS number as a new internal mobile number.
     * @param $number string The SMS-capable mobile phone number
     * 
     * @return Phone
     */
    public function addSmsNumber($number)
    {
        $currentNumber = $this->getSmsNumber();
        if ($currentNumber) {
            $this->unsetSmsNumber();
        }
        if (!$this->data->phone) {
            $this->data->phone = [];
        }
        $phone_obj = (object) [
            'phone_number' => $number,
            'preferred' => false,
            'preferred_sms' => true,
            'segment_type' => 'Internal',
            'phone_type' => [(object) [
                'value' => 'mobile',
                'desc' => 'Mobile'
            ]]
        ];
        $this->data->phone[] = $phone_obj;
        return Phone::make($this->client, $phone_obj);
    }

    /**
     * Adds a new internal phone number to the user
     * 
     * @param string $phone_number The phone number
     * @param string $phone_type Type of the phone number (home, mobile, etc.)
     * @param bool $preferred Whether this should be the user's preferred phone number
     * 
     * @return Phone
     */
    public function addPhone($phone_number, $phone_type, $preferred = false)
    {
        if (!$this->data->phone) {
            $this->data->phone = [];
        }
        if ($preferred) {
            $this->unsetPreferredPhone();
        }
        $phone_obj = (object) [
            'phone_number' => $phone_number,
            'preferred' => $preferred,
            'preferred_sms' => false,
            'segment_type' => 'Internal',
            'phone_type' => [(object) [
                'value' => $phone_type
            ]]
        ];
        $this->data->phone[] = $phone_obj;
        return Phone::make($this->client, $phone_obj);
    }

    /**
     * Gets the user's preferred phone number, or null if none are preferred
     * 
     * @return Phone|null
     */
    public function getPreferredPhone()
    {
        foreach ($this->data->phone as $phone) {
            if ($phone->preferred) {
                return Phone::make($this->client, $phone);
            }
        }
        return null;
    }

    /**
     * Remove the preferred flag from all phone numbers
     */
    public function unsetPreferredPhone()
    {
        foreach ($this->data->phone as $phone) {
            if ($phone->preferred) {
                $phone->preferred = false;
            }
        }
    }

    /**
     * Sets the given phone number as the user's preferred number, adding it as an internal home phone if necessary
     * 
     * @param string $phone_number The phone number
     * 
     * @throws Exception when the given phone number is not found in the user
     */
    public function setPreferredPhone($phone_number)
    {
        $current_phone = $this->getPreferredPhone();
        if ($current_phone && $phone_number === $current_phone->phone_number) {
            return;
        }
        $this->unsetPreferredPhone();
        foreach ($this->data->phone as $phone) {
            if ($phone->phone_number === $phone_number) {
                $phone->preferred = true;
                return;
            }
        }
        throw new Exception('Phone number ' . $phone_number . ' not found in user');
    }

    /**
     * Removes a phone number from the user
     * 
     * @param string The phone number
     */
    public function removePhone($phone_number)
    {
        foreach ($this->data->phone as $key => $phone) {
            if ($phone->phone_number === $phone_number) {
                array_splice($this->data->phone, $key, 1);
                return;
            }
        }
    }

    /**
     * Returns an array of all phone numbers associated with the user
     * 
     * @return array An array of Phone objects
     */
    public function allPhones()
    {
        $phones = [];
        foreach ($this->data->phone as $phone) {
            $phones[] = Phone::make($this->client, $phone);
        }
        return $phones;
    }

    /**
     * Gets the user's preferred email address
     * @return Email The email address
     */
    public function getEmail()
    {
        if ($this->data->email) {
            foreach ($this->data->email as $email) {
                if ($email->preferred) {
                    return Email::make($this->client, $email);
                }
            }
        }
        return null;
    }

    /**
     * Sets the user's preferred email address, adding a new email address if needed.
     * @param string $email_address The email address
     * 
     * @throws Exception when the given email address is not found in the user
     */
    public function setEmail($email_address)
    {
        $current_email = $this->getEmail();
        if ($current_email && $email_address === $current_email->email_address) {
            return;
        }
        $this->unsetEmail();
        if ($this->data->email) {
            foreach ($this->data->email as $email) {
                if ($email->email_address === $email_address) {
                    $email->preferred = true;
                    return;
                }
            }
        }
        throw new Exception('Email address ' . $email_address . ' not found in user');
    }

    /**
     * Removes the preferred flag from all email addresses
     */
    public function unsetEmail()
    {
        if ($this->data->email) {
            foreach ($this->data->email as $email) {
                if ($email->preferred) {
                    $email->preferred = false;
                }
            }
        }
    }

    /**
     * Adds a new email address
     * @param string $email_address The email address
     * @param string $email_type The email type, defaults to 'personal'
     * @param bool $preferred True if this should be the preferred email
     * 
     * @return Email
     */
    public function addEmail($email_address, $email_type = 'personal', $preferred = false)
    {
        if (!$this->data->email) {
            $this->data->email = [];
        }
        if ($preferred) {
            $this->unsetEmail();
        }
        $email_obj = (object) [
            'preferred' => $preferred,
            'segment_type' => 'Internal',
            'email_address' => $email_address,
            'description' => '',
            'email_type' =>[(object) [
                'value' => $email_type,
            ]]
        ];
        $this->data->email[] = $email_obj;
        return Email::make($this->client, $email_obj);
    }

    /**
     * Removes the given email address from the user
     * 
     * @param string $email_address The email address to remove
     */
    public function removeEmail($email_address)
    {
        foreach ($this->data->email as $key => $email) {
            if ($email->email_address === $email_address) {
                array_splice($this->data->email, $key, 1);
                return;
            }
        }
    }

    /**
     * Returns an array of all email addresses associated with the user
     * 
     * @return array An array of Email objects
     */
    public function allEmails()
    {
        $emails = [];
        foreach ($this->data->email as $email) {
            $emails[] = Email::make($this->client, $email);
        }
        return $emails;
    }

    /**
     * Get an array of objects representing the user's addresses
     * 
     * @return array An array of Address objects
     */
    public function getAddresses() {
        $addresses = [];
        foreach ($this->address as $address) {
            $addresses[] = Address::make($this->client, $address);
        }
        return $addresses;
    }

    /**
     * Adds a new address.
     * 
     * @param array $address The address' properties
     * @return Address A new address object based on the given values
     */
    public function addAddress($address)
    {
        if (isset($address['country']) && is_string($address['country'])) {
            $address['country'] = (object) ['value' => $address['country']];
        }
        if (isset($address['address_type'])) {
            if (is_string($address['address_type'])) {
                $address['address_type'] = [(object)['value' => $address['address_type']]];
            } elseif (is_object($address['address_type'])) {
                $address['address_type'] = [$address['address_type']];
            }
        }
        if (!$this->data->address) {
            $this->data->address = [];
        }
        $address = (object) $address;
        $this->data->address[] = $address;
        return Address::make($this->client, $address);
    }

    /**
     * Removes an address from the user
     * 
     * @param Address|stdClass $address Either the Address object to be removed or its underlying stdClass object
     */
    public function removeAddress($address)
    {
        if ($address instanceof Address) {
            $address = $address->data;
        }
        if (($key = array_search($address, $this->data->address)) !== false) {
            array_splice($this->data->address, $key, 1);
        }
    }

    /**
     * Returns the user's preferred address
     * 
     * @return Address|null The address object or null if none are preferred
     */
    public function getPreferredAddress()
    {
        foreach ($this->data->address as $address) {
            if ($address->preferred) {
                return Address::make($this->client, $address);
            }
        }
        return null;
    }

    /**
     * Removes the preferred flag from all addresses
     */
    public function unsetPreferredAddress()
    {
        foreach ($this->data->address as $address) {
            if ($address->preferred) {
                $address->preferred = false;
            }
        }
    }
}
