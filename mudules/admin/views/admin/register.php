<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="viewport"
	content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>注册丨Sharelink</title>
<link rel="stylesheet" href="/loginweb/style.css">
</head>
<body>

	<div class="register-container">
		<h1>Kevi</h1>

		<div class="connect">
			<p style="left: 0%;">欢迎注册kevi 账号</p>
		</div>

		<form novalidate="novalidate" action="" method="post"
			id="registerForm">
			<div>
				<input name="username" class="username" placeholder="您的用户名"
					autocomplete="off" type="text">
			</div>
			<div>
				<input name="password" class="password" placeholder="输入密码"
					oncontextmenu="return false" onpaste="return false" type="password">
			</div>
			<div>
				<input name="confirm_password" class="confirm_password"
					placeholder="再次输入密码" oncontextmenu="return false"
					onpaste="return false" type="password">
			</div>
			<div>
				<input name="phone_number" class="phone_number" placeholder="输入手机号码"
					autocomplete="off" id="number" type="text">
			</div>
			<div>
				<input name="email" class="email" placeholder="输入邮箱地址"
					oncontextmenu="return false" onpaste="return false" type="email">
			</div>

			<button id="submit" type="submit">注 册</button>
		</form>
		<a href="/admin/admin/index">
			<button type="button" class="register-tis">已经有账号？</button>
		</a>

	</div>


	<script src="/loginweb/jquery_002.js"></script>
	<script src="/loginweb/common.js"></script>
	<!--背景图片自动更换-->
	<script src="/loginweb/supersized.js"></script>
	<script src="/loginweb/supersized-init.js"></script>
	<!--表单验证-->
	<script src="/loginweb/jquery.js"></script>
	<ul style="visibility: visible;" class="speed" id="supersized">
		<li style="visibility: visible; opacity: 1;" class="slide-0 prevslide"><a
			target="_blank"><img
				style="width: 1272px; left: 0px; top: -113.5px; height: 801.36px;"
				src="/loginweb/1.jpg"></a></li>
		<li style="visibility: visible; opacity: 0.00856446;"
			class="slide-1 activeslide"><a target="_blank"><img
				style="width: 1272px; left: 0px; top: -158px; height: 890.4px;"
				src="/loginweb/2.jpg"></a></li>
		<li style="visibility: visible; opacity: 1;" class="slide-2"><a
			target="_blank"><img
				style="width: 1272px; height: 801.36px; left: 0px; top: -113.5px;"
				src="/loginweb/3.jpg"></a></li>
	</ul>
</body>
</html>