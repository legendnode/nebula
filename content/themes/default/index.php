<?php $this->render('modules/header'); ?>
<?php $user = \Nebula\Widgets\User::factory(); ?>
<div class="container">
    <div>这是主题 default</div>
    <?php if ($user->hasLogin()) : ?>
        欢迎：<?= $user->get('username') ?>
        <a href="/user/logout">退出登录</a>
        <a href="/admin">后台管理</a>
    <?php else : ?>
        <a href="/admin/login.php">登录</a>
    <?php endif; ?>
</div>
<div>主题参数：<?php print_r($data['theme_config']) ?></div>
<ul>
    <?php foreach (\Nebula\Widgets\Content::factory()->queryContents() as $item) : ?>
        <li><a href="/article/<?= $item['cid'] ?>"><?= $item['title'] ?></a></li>
    <?php endforeach; ?>
</ul>
<?php $this->render('modules/footer'); ?>
