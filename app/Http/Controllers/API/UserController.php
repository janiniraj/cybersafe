<?php namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Repositories\User\EloquentUserRepository;
use App\Http\Transformers\UserTransformer;
use Auth;
use App\Http\Utilities\PushNotification;

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
        $this->notification     = new PushNotification;
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

        foreach($transformedUserData['family'] as $key => $value)
        {
            $transformedUserData['family'][$key]['emergency'] = $this->userRepository->checkForEmergency($value['id']);
        }
        
        return $this->successResponse($transformedUserData);
    }

    /**
     * Add Chat Room Id
     *
     * @param $chatroomId
     * @param Request $request
     * @return json|string
     */
    public function addChatRoomId($chatroomId, Request $request)
    {
        $user = Auth::user();

        $result = $this->userRepository->updateChatRoomId($chatroomId, $user->family_code);
        if($result)
        {
            return $this->profile($request);
        }
        else
        {
            return $this->failureResponse([], 'Error in adding Chatroom Id.');
        }
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

    /**
     * Emergency
     *
     * @param Request $request
     * @return json|string
     */
    public function emergency(Request $request)
    {
        $user = Auth::user();

        if($user->type != 'child')
        {
            return $this->failureResponse([], 'Parents can not use this functionality.');
        }

        $checkForEntry = $this->userRepository->checkForEmergency($user->id);

        if($checkForEntry)
        {
            return $this->failureResponse([], 'Emergency is already created.');
        }

        $emergencyFlag = $this->userRepository->createEmergency($user->id);

        if(!$emergencyFlag)
        {
            return $this->failureResponse([], 'Error in creating emergency entry.');
        }

        $parents = $this->userRepository->getParents($user->family_code);

        foreach ($parents as $singleParent)
        {
            if($singleParent->device_token)
            {
                $message        = "Your Child ".$user->name." is in Danger";
                $extraFields    = ['code' => $user->code, 'family_code' => $user->family_code, 'emergency' => true];

                $this->notification->_pushNotification($message, $singleParent->device_type, $singleParent->device_token, $extraFields);
            }
        }

        return $this->successResponse([], 'Emergency Message Sent Successfully.');
    }

    public function dismissEmergency($code, Request $request)
    {
        $user = Auth::user();

        $child = $this->userRepository->getUserByCode($code);

        if(empty($child))
        {
            return $this->failureResponse([], 'No Child Found.');
        }

        $check = $this->userRepository->checkForEmergency($child->id);

        if(!$check)
        {
            return $this->failureResponse([], 'No Emergency is created for this child.');
        }

        $flag = $this->userRepository->dismissEmergency($child->id, $user->id);

        if($flag)
        {
            return $this->successResponse([], 'Emergency successfully Dismissed.');
        }
        else
        {
            return $this->failureResponse([], 'Error in Dismissing Emergency.');
        }
    }
}
