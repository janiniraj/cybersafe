<?php namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Repositories\User\EloquentUserRepository;
use App\Http\Transformers\UserTransformer;
use Auth;

class UserController extends BaseApiController
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->userRepository   = new EloquentUserRepository();
        $this->transformer      = new UserTransformer();
    }

    /**
     * Profile
     *
     * @param Request $request
     * @return json|string
     */
    public function profile(Request $request)
    {
        $user                           = Auth::user();
        $transformedUserData            = $this->transformer->transform($user->toArray());
        $familyData                     = $this->userRepository->fetchFamilyDataByFamilyCode($user->family_code)->toArray();
        $transformedUserData['family']  = $this->transformer->transformCollection($familyData);

        if($user->is_admin)
        {
            $homeData = $user->address;
        }
        else
        {
            $homeData = $this->userRepository->findHomeAddress($user->family_code);
        }

        $transformedUserData['home'] = $this->transformer->transformHomeLocation($homeData);

        return $this->successResponse($transformedUserData);
    }

    /**
     * Fetch Family
     *
     * @param Request $request
     * @param $code
     * @return json|string
     */
    public function fetchFamily(Request $request, $code)
    {
        $familyData = $this->userRepository->fetchFamilyDataByCode($code)->toArray();
        return $this->successResponse($this->transformer->transformCollection($familyData));
    }
}
