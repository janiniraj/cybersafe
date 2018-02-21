<?php
namespace App\Http\Transformers;

/**
 * Class UserLocationTransformer
 * @package App\Http\Transformers
 */
class UserLocationTransformer extends Transformer
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
            'latitude'      => $this->nulltoBlank($data['latitude']),
            'longitude'     => $this->nulltoBlank($data['longitude']),
            'address_name'  => $this->nulltoBlank($data['address_name']),
            'address'       => $this->nulltoBlank($data['address']),
            'created_at'    => $data['created_at']
        ];
    }

    public function transformFamily($data)
    {
        return [
            'id'            => $data['id'],
            'user_id'       => $data['user_id'],
            'latitude'      => $this->nulltoBlank($data['latitude']),
            'longitude'     => $this->nulltoBlank($data['longitude']),
            'address_name'  => $this->nulltoBlank($data['address_name']),
            'address'       => $this->nulltoBlank($data['address']),
            'created_at'    => $data['created_at'],
            'name'          => $this->nulltoBlank($data['name']),
            'code'          => $this->nulltoBlank($data['code']),
        ];
    }
}