<?php
namespace App\Http\Transformers;

/**
 * Class UserTransformer
 * @package App\Http\Transformers
 */
class UserTransformer extends Transformer
{
    /**
     * Transform
     *
     * @param $data
     * @return array
     */
    public function transform($data)
    {
        return [
            'id'            => $data['id'],
            'name'          => $this->nulltoBlank($data['name']),
            'phone'         => $this->nulltoBlank($data['phone']),
            'code'          => $this->nulltoBlank($data['code']),
            'family_code'   => $this->nulltoBlank($data['family_code']),
            'is_admin'      => $data['is_admin'],
            'type'          => $this->nulltoBlank($data['type']),
            'chatroom_id'   => $this->nulltoBlank($data['chatroom_id'])
        ];
    }

    /**
     * Transform User With Token
     *
     * @param $data
     * @param $token
     * @return array
     */
    public function transformUserWithToken($data, $token)
    {
        return array_merge($this->transform($data),['token' => $token]);
    }

    /**
     * Transform Home Location
     *
     * @param $data
     * @return array
     */
    public function transformHomeLocation($data)
    {
        return [
            'latitude'      => $this->nulltoBlank($data['latitude']),
            'longitude'     => $this->nulltoBlank($data['longitude']),
            'address_name'  => $this->nulltoBlank($data['address_name']),
            'address'       => $this->nulltoBlank($data['address'])
        ];
    }
}