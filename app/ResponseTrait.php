<?php

namespace App;

trait ResponseTrait
{
    public function response($key, $msg, $data = [], $anotherKey = [], $page = false)
	{

		$allResponse['key']   = (string)$key;
		$allResponse['msg']   = (string)$msg;

		# unread notifications count if request ask
		if ('success' == $key && request()->has('count_notifications')) {
			$count = 0;
			if (auth()->check()) {
				$notifications = auth()->user()->unreadNotifications();

				$count = $notifications->count();
			}

			$allResponse['count_notifications'] = $count;
			if ($count > 0) {
				$notifications->each(function ($notification) {
					$notification->markAsRead();
				});
			}
		}
		# additional data
		if (!empty($anotherKey)) {
			foreach ($anotherKey as $otherkey => $value) {
				$allResponse[$otherkey] = $value;
			}
		}
		# res data
		if ([] != $data && (in_array($key, ['success', 'needActive', 'exception', 'need_worktimes', 'pending_teacher']))) {
			$allResponse['data'] = $data;
		}

		return response()->json($allResponse);
	}

    public function unauthenticatedReturn()
	{
		return $this->response('unauthenticated', trans('auth.unauthenticated'));
	}

    public function failMsg($msg)
	{
		return $this->response('fail', $msg);
	}

    public function successMsg($msg = 'done')
	{
		return $this->response('success', $msg);
	}

	public function successData($data)
	{
		return $this->response('success', trans('apis.success'), $data);
	}

    public function getCode($key)
	{
		switch ($key) {
			case 'success':
				$code = 200;
				break;
			case 'fail':
				$code = 400;
				break;
			case 'needActive':
				$code = 203;
				break;
			case 'unauthorized':
				$code = 400;
				break;
			case 'unauthenticated':
				$code = 401;
				break;
			case 'blocked':
				$code = 423;
				break;
			case 'exception':
				$code = 500;
				break;

			default:
				$code = 200;
				break;

		}

		return $code;
	}


}
