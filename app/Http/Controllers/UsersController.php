<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Http\Requests;
use App\Models\User;

class UsersController extends Controller
{

	/**
	 * 个人资料页面
	 * @param  User   $user [description]
	 * @return [type]       [description]
	 */
	public function show(User $user)
	{
		return view('users.show',compact('user'));
	}

	/**
	 * 注册页面
	 * @return [type] [description]
	 */
    public function create()
    {
    	return view('users.create');
    }

    /**
     * 注册
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store(Request $request)
    {
    	//unique:users 是针对users表做唯一验证， 为了严谨，users表中也要设置email字段为唯一
    	$this->validate($request, [
	        'name' => 'required|max:50',
	        'email' => 'required|email|unique:users|max:255',
	        'password' => 'required|confirmed|min:6'
	    ]);

    	// 用户模型 User::create() 创建成功后会返回一个用户对象，并包含新注册用户的所有信息。
	    $user = User::create([
	   		'name' => $request->name,
	   		'email' => $request->email,
	   		'password' => bcrypt($request->password),
	   	]);

	    session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');

	    // 这里是一个『约定优于配置』的体现，此时 $user 是 User 模型对象的实例。route() 方法会自动获取 Model 的主键，也就是数据表 users 的主键 id，以上代码等同于： redirect()->route('users.show', [$user->id]);

	   	return redirect()->route('users.show',[$user]);
    }
}
