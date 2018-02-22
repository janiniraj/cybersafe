<?php namespace App\Repositories\UserLocation;

use App\Models\UserLocation\UserLocation;
use App\Models\User\User;
use App\Repositories\DbRepository;
use App\Exceptions\GeneralException;

class EloquentUserLocationRepository extends DbRepository implements UserLocationRepositoryContract
{
	/**
	 * UserLocation Model
	 * 
	 * @var Object
	 */
	public $model;

	/**
	 * Module Title
	 * 
	 * @var string
	 */
	public $moduleTitle = 'User Location';

	/**
	 * Construct
	 *
	 */
	public function __construct()
	{
		$this->model        = new UserLocation;
		$this->userModel    = new User;
	}

	/**
	 * Create UserLocation
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
	 * Update UserLocation
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
	 * Destroy UserLocation
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
     * Get All By User
     *
     * @param string $userId
     * @param integer $limit
     * @param string $sort
     * @return mixed
     */
    public function getAllByUser($userId, $limit, $sort = 'desc')
    {
        $result = $this->model->where([
            ['user_id', '=', $userId],
            ['address_name', '!=', 'null'],
            ['address_name', '!=', ''],
            ['address', '!=', 'null'],
            ['address', '!=', '']
        ])->groupBy('address_name')->orderBy('id', $sort);

        if($limit)
        {
            $result = $result->limit($limit);
        }

        return $result->get();
    }

    /**
     * Fetch Recent Family Locations
     *
     * @param $familyCode
     * @return \Illuminate\Support\Collection
     */
    public function getRecentFamilyLocations($familyCode)
    {
        $result = $this->model
            ->select('user_locations.*', 'users.name', 'users.code')
            ->join('users', 'users.id', '=', 'user_locations.user_id')
            ->where('family_code', $familyCode)
            ->groupBy('user_locations.user_id')
            ->orderBy('id', 'DESC')
            ->get();

        return $result;
    }

    public function getRecentLocationByUserCode($code)
    {
        $result = $this->model
            ->select('user_locations.*', 'users.name', 'users.code')
            ->join('users', 'users.id', '=', 'user_locations.user_id')
            ->where('code', $code)
            ->orderBy('id', 'DESC')
            ->first();

        return $result;
    }
}