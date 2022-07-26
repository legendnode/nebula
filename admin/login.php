<?php include __DIR__ . '/modules/common.php'; ?>
<?php $user->hasLogin() && $response->redirect('/'); ?>
<?php include __DIR__ . '/modules/header.php'; ?>
<div class="signup">
    <div class="signup-wrapper">
        <h1 class="signup-title">Nebula</h1>
        <form class="form" action="/user/login" method="POST">
            <div class="form-item">
                <input class="input" type="text" name="account" placeholder="用户名" value="<?= $cache->get('loginAccount', '') ?>">
            </div>
            <div class="form-item">
                <input class="input" type="password" name="password" placeholder="密码">
            </div>
            <button type="submit" class="button button-block">登录</button>
        </form>
        <?php \Nebula\Plugin::factory('admin/login.php')->button(); ?>
        <div class="signup-tools">
            <a href="/">返回首页</a>
            <?php if ($option->get('allowRegister')) : ?>
                <a href="/admin/register.php">立即注册</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/modules/common-js.php'; ?>
<?php include __DIR__ . '/modules/footer.php'; ?>
