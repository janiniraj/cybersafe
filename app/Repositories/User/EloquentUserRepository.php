<?php namespace App\Repositories\User;

use App\Models\User\User;
use App\Models\UserAddress\UserAddress;
use App\Repositories\DbRepository;
use App\Exceptions\GeneralException;

class EloquentUserRepository extends DbRepository implements UserRepositoryContract
{
	/**
	 * User Model
	 * 
	 * @var Object
	 */
	public $model;

	/**
	 * Module Title
	 * 
	 * @var string
	 */
	public $moduleTitle = 'User';

	/**
	 * Construct
	 *
	 */
	public function __construct()
	{
		$this->model    = new User;
		$this->address  = new UserAddress;
	}

	/**
	 * Create User
	 *
	 * @param array $input
	 * @return mixed
	 */
	public function create($input)
	{
		$input = $this->prepareInputData($input, true);
		$model = $this->model->create($input);

		if($model)
		{
			return $model;
		}

		return false;
	}	

	/**
	 * Update User
	 *
	 * @param int $id
	 * @param array $input
	 * @return bool|int|mixed
	 */
	public function update($id, $input)
	{
		$model = $this->model->find($id);

		if($model)
		{
			$input = $this->prepareInputData($input);		
			
			return $model->update($input);
		}

		return false;
	}

	/**
	 * Destroy User
	 *
	 * @param int $id
	 * @return mixed
	 * @throws GeneralException
	 */
	public function destroy($id)
	{
		$model = $this->model->find($id);
			
		if($model)
		{
			return $model->delete();
		}

		return  false;
	}

	/**
     * Get All
     *
     * @param string $orderBy
     * @param string $sort
     * @return mixed
     */
    public function getAll($orderBy = 'id', $sort = 'asc')
    {
        return $this->model->all();
    }

	/**
     * Get by Id
     *
     * @param int $id
     * @return mixed
     */
    public function getById($id = null)
    {
    	if($id)
    	{
    		return $this->model->find($id);
    	}
        
        return false;
    }

    /**
     * Get by Code
     *
     * @param int $code
     * @return mixed
     */
    public function getByCode($code = null)
    {
        if($code)
        {
            return $this->model->where('code', $code)->first();
        }

        return false;
    }

    /**
     * Prepare Input Data
     * 
     * @param array $input
     * @param bool $isCreate
     * @return array
     */
    public function prepareInputData($input = array(), $isCreate = false)
    {

    	return $input;
    }

    /**
     * Check for Duplicate Phone
     *
     * @param $phone
     * @return bool
     */
    public function checkForDuplicatePhone($phone)
    {
        $result = $this->model->where('phone', $phone)->count();

        return $result > 0 ? true : false;
    }

    /**
     * Create Parent
     *
     * @param $data
     * @return $this|bool|\Illuminate\Database\Eloquent\Model
     */
    public function createParent($data)
    {
        $data['is_admin']       = 1;
        $data['password']       = bcrypt($data['password']);
        $data['status']         = 1;
        $data['code']           = $this->generateRandomString(6, true);
        $data['family_code']    = $this->generateRandomString(6, true);
        $data['is_admin']       = 1;
        $data['type']           = 'parent';

        $guardians              = isset($data['guardians']) ? json_decode($data['guardians'], true) : [];
        $children               = isset($data['children']) ? json_decode($data['children'], true) : [];

        $model = $this->model->create($data);

        if($model)
        {
            $this->address->create([
                'user_id'       => $model->id,
                'latitude'      => $data['home_latitude'],
                'longitude'     => $data['home_longitude'],
                'address_name'  => $data['home_address_name'],
                'address'       => $data['home_address']
            ]);

            if(!empty($guardians))
            {
                $this->saveGuardiansOrChildren($guardians, 'parent', $model->family_code, $model->id);
            }

            if(!empty($children))
            {
                $this->saveGuardiansOrChildren($children, 'child', $model->family_code, $model->id);
            }

            return $model;
        }

        return false;
    }

    /**
     * Save Guardian or Children
     *
     * @param $data
     * @param $type
     * @param null $familyCode
     * @param null $parentId
     * @return bool
     */
    public function saveGuardiansOrChildren($data, $type, $familyCode = NULL, $parentId = NULL)
    {
        if(!$familyCode)
        {
            $parentData = $this->model->find($parentId);
            $familyCode = $parentData->family_code;
        }

        foreach($data as $singleData)
        {
            $dataToSave = [
                'phone'         => $singleData['phone'],
                'name'          => $singleData['name'],
                'password'      => $type == 'parent' ? bcrypt(env('PARENT_PASSWORD')) : bcrypt(env('CHILD_PASSWORD')),
                'code'          => $this->generateRandomString(6, true),
                'family_code'   => $familyCode,
                'type'          => $type,
                'status'        => 1,
                'is_admin'      => 0
            ];

            $this->model->create($dataToSave);
        }

        return true;
    }

    /**
     * Fetch Family Data By Code
     *
     * @param $code
     * @return \Illuminate\Support\Collection
     */
    public function fetchFamilyDataByCode($code)
    {
        $familyCodeRequest = $this->getByCode($code);
        return $this->fetchFamilyDataByFamilyCode($familyCodeRequest->family_code);
    }

    /**
     * Fetch Family Data By Family Code
     *
     * @param $familyCode
     * @return \Illuminate\Support\Collection
     */
    public function fetchFamilyDataByFamilyCode($familyCode)
    {
        return $this->model->where('family_code', $familyCode)->get();
    }

    public function findHomeAddress($familyCode)
    {
        return $this->model->where(['family_code' => $familyCode, 'is_admin' => 1])->first()->address;
    }
}