<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Mail;
use Auth;

class UsersController extends Controller
{
	//是否登录的校验。以下页面
	public function __construct()
	{
		// 我们提倡在控制器 Auth 中间件使用中，首选 except 方法，这样的话，当你新增一个控制器方法时，默认是安全的，此为最佳实践
		
        //未登录用户都可访问的页面
        $this->middleware('auth',[
			'except' => ['show','create','store','index','confirmEmail']
		]);

        $this->middleware('guest',[
            'only' => ['create']
        ]);
	}

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

	    // Auth::login($user);
        $this->sendEmailConfirmationTo($user);

	    session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');

	    // 这里是一个『约定优于配置』的体现，此时 $user 是 User 模型对象的实例。route() 方法会自动获取 Model 的主键，也就是数据表 users 的主键 id，以上代码等同于： redirect()->route('users.show', [$user->id]);

	   	return redirect()->route('users.show',$user);
    }

    /**
     * 编辑资料页面
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function edit(User $user)
    {
    	$this->authorize('update',$user); //判定操作的是否是自己
    	return view('users.edit',compact('user'));
    }

    /**
     * 更新资料
     * @param  User    $user    [description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function update(User $user, Request $request)
    {
    	$this->validate($request,[
    		'name' => 'required|max:50',
    		'password' => 'nullable|confirmed|min:6'
    	]);

    	// 这里 update 是指授权类里的 update 授权方法
    	// $user 对应传参 update 授权方法的第二个参数
    	// 调用时，默认情况下，我们 不需要 传递第一个参数，也就是当前登录用户至该方法内，因为框架会自动加载当前登录用户。
    	$this->authorize('update', $user);

    	$data = [];
    	$data['name'] = $request->name;
    	//填写了密码才更新
    	if( $request->password ){
    		$data['password'] = bcrypt($request->password);
    	}
    	$user->update($data);

    	session()->flash('success','个人资料更新成功');

    	return redirect()->route('users.show',$user->id);
    }

    /**
     * 所有用户
     * @return [type] [description]
     */
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index',compact('users'));
    }

    /**
     * 删除用户
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function destroy(User $user)
    {
        $this->authorize('destroy',$user);
        $user->delete();
        session()->flash('success','成功删除用户！');
        return back();
    }

    /**
     * 发送邮件
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'aufree@yousails.com';
        $name = 'Aufree';
        $to = $user->email;
        $subject = '感谢注册 Sample 应用！请确认你的邮箱。';

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    /**
     * 激活
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();
        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success','恭喜你，注册成功');        
        return redirect()->route('users.show', [$user]);
    }

}
