

<?php if(isset($validation)):?>
    <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
<?php endif;?>
<form class="bg-white rounded shadow-5-strong p-5" action="/register/save" method="post">
    <h3>Sign Up</h3><br>
    <div class="form-outline mb-4">
        <input type="text" name="username" class="form-control" id="InputForName" value="<?= set_value('username') ?>">
        <label for="InputForName" class="form-label">Username</label>
    </div>
    <div class="form-outline mb-4">
        <input type="email" name="email" class="form-control" id="InputForEmail" value="<?= set_value('email') ?>">
        <label for="InputForEmail" class="form-label">Email address</label>
    </div>
    <div class="form-outline mb-4">
        <input type="password" name="password" class="form-control" id="InputForPassword">
        <label for="InputForPassword" class="form-label">Password</label>
    </div>
    <div class="form-outline mb-4">
        <input type="password" name="confpassword" class="form-control" id="InputForConfPassword">
        <label for="InputForConfPassword" class="form-label">Confirm Password</label>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>
