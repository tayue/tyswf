<?php
/* Smarty version 3.1.34-dev-7, created on 2020-01-03 17:49:25
  from '/home/wwwroot/default/myframework/App/View/Home/Index/index.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.34-dev-7',
  'unifunc' => 'content_5e0f0e25784b60_90336375',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f91d603a1c3bf2def80e1b4c056fb94fa4e418c0' => 
    array (
      0 => '/home/wwwroot/default/myframework/App/View/Home/Index/index.html',
      1 => 1543370922,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5e0f0e25784b60_90336375 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $_smarty_tpl->tpl_vars['name']->value;?>
</title>
    <?php echo '<script'; ?>
 src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"><?php echo '</script'; ?>
>
</head>
<body>
<div>
    <h2 id="dd"><?php echo $_smarty_tpl->tpl_vars['name']->value;?>
</h2>
</div>

<form action="" method="post" autocomplete="on" id="from">
    First name: <input type="text" name="fname" /><br />
    Last name: <input type="text" name="lname" /><br />
    E-mail: <input type="email" name="email" autocomplete="off" /><br />
    <input type="button" onclick="submitform()" />
</form>
</body>
</html>


<?php echo '<script'; ?>
>
    $(function () {

    });

    function submitform() {
        var url="\\home\\Index\\indexs";
        $.ajax({
            type:'post',
            url:url,
            data:$('#from').serializeArray(),
            dataType:'json',
            success:function (data) {
                console.log(data);
            }
        });
    }

<?php echo '</script'; ?>
><?php }
}
