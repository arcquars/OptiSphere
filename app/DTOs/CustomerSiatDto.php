<?php

namespace App\DTOs;

use Brick\Math\BigInteger;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;

/**
 * Data Transfer Object para estructurar el payload de la API de creación de facturas.
 */
class CustomerSiatDto implements Arrayable
{
    public string $code;
    public int $groupId;
    public int $storeId;
    public string $firstname;
    public string $lastname;

    public string $identityDocument;
    public string $company;
    public string $dateOfBirth;
    public ?string $gender;
    public string $phone;
    public string $fax;
    public string $email;
    public string $website;
    public string $address1;
    public string $address2;
    public string $zipCode;
    public string $city;
    public string $country;

    public string $countryCode;
    public array $meta;

    /**
     * Constructor del DTO.
     * @param array $data Datos de entrada.
     * @throws ValidationException Si faltan campos requeridos o son de tipo incorrecto.
     */
    public function __construct(array $data)
    {
        $this->code = (string) $data['code'];
        $this->groupId = $data['groupId']?? -1;
        $this->storeId = $data['storeId'];
        $this->firstname = (string) $data['firstname'];
        $this->lastname = (string) $data['lastname'];
        $this->identityDocument = (string) $data['identityDocument'];
        $this->company = (string) $data['company'];
        $this->dateOfBirth = "";
        $this->gender = isset($data['gender'])? (string) $data['gender'] : null;
        $this->phone = (string) $data['phone'];
        $this->fax = isset($data['fax'])? (string) $data['fax'] : '';
        $this->email = (string) $data['email'];
        $this->website = isset($data['website'])? (string) $data['website'] : '';
        $this->address1 = isset($data['address1'])? (string) $data['address1'] : "";
        $this->address2 = isset($data['address2'])? (string) $data['address2'] : "";
        $this->zipCode = isset($data['zipCode'])? (string) $data['zipCode'] : "";
        $this->city = isset($data['city'])? (string) $data['city'] : "";
        $this->country = isset($data['country'])? (string) $data['country'] : 'Bolivia';
        $this->countryCode = isset($data['countryCode'])? (string) $data['countryCode'] : 'BO';
        if(is_array($data['meta']))
            $this->meta = $data['meta'];
        else    
            $this->meta = [];
    }

    /**
     * Convierte el DTO a un array para ser enviado como payload JSON.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'group_id' => $this->groupId,
            'store_id' => $this->storeId,
            'first_name' => $this->firstname,
            'last_name' => $this->lastname,
            'identity_document' => (string) $this->identityDocument,
            'company' => $this->company,
            'date_of_birth' => $this->dateOfBirth,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'email' => $this->email,
            'website' => $this->website,
            'address_1' => $this->address1,
            'address_2' => $this->address2,
            'zip_code' => $this->zipCode,
            'city' => $this->city,
            'country' => $this->country,
            'country_code' => $this->countryCode,
            'meta' => $this->meta,
        ];
    }
}