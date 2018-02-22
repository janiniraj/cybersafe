<?php namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Repositories\UserLocation\EloquentUserLocationRepository;
use App\Http\Transformers\UserLocationTransformer;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Auth;
use App\Repositories\User\EloquentUserRepository;

class UserLocationController extends BaseApiController
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->repository   = new EloquentUserLocationRepository();
        $this->transformer  = new UserLocationTransformer();
        $this->userRepository = new EloquentUserRepository();
    }

    /**
     * Add User Location
     *
     * @param Request $request
     * @return json|string
     */
    public function add(Request $request)
    {
        //Validate Request
        $validator = Validator::make($request->all(), [
            'latitude'     => 'required',
            'longitude'    => 'required',
            //'address'      => 'required',
            //'address_name' => 'required'
        ]);

        if ($validator->fails())
        {
            return $this->failureResponse([], 'Invalid Input Parameters.');
        }

        $data               = $request->all();
        $data['user_id']    = Auth::user()->id;

        if($this->repository->create($data))
        {
            return $this->successResponse([], 'Location Saved Successfully.');
        }
        else
        {
            return $this->failureResponse([], 'Error in Saving Location');
        }
    }

    /**
     * Fetch Location
     *
     * @param Request $request
     * @param $userCode
     * @return json|string
     */
    public function fetchLocation(Request $request, $userCode)
    {
        $userData = $this->userRepository->getUserByCode($userCode);
        $data = $this->repository->getAllByUser($userData->id, env('LOCATION_RECORD_LIMIT'))->toArray();

        return $this->successResponse($this->transformer->transformCollection($data));
    }

    /**
     * Fetch Recent Family Locations
     *
     * @param Request $request
     * @return json|string
     */
    public function fetchRecentLocationOfFamily(Request $request)
    {
        $user = Auth::user();

        $data = $this->repository->getRecentFamilyLocations($user->family_code)->toArray();

        return $this->successResponse($this->transformer->transformCollection($data, 'transformFamily'));
    }

    /**
     * Fetch Recent Location of User
     *
     * @param $code
     * @param Request $request
     * @return json|string
     */
    public function fetchRecentLocation($code, Request $request)
    {
        $data = $this->repository->getRecentLocationByUserCode($code)->toArray();

        return $this->successResponse($this->transformer->transformFamily($data));
    }
}
