这是一个轻量级的php过滤参数的包,由于前端的不可信任,
后端必须在接受参数后进行验证.而参数一多,验证的代码将变得非常冗杂,
让代码更难以阅读.

虽然php自带有filter函数,但是里面定义的东西太多,使用起来也非常复杂.
而这个verifier,使用起来非常简单,并且是他是具有成长性的,也就是说你完全可以
根据自己的需要去定义他里面的规则,从而提高项目的可读性

快速使用:
```
use Validate\Validate;
~
$v = new Validate();
//validate有自带的规则,但是你也可以使用php自带的函数
//require代表参数必传
$rules = array('参数名'=>'规则名|报错信息',require=>array('参数名'));
$map = $v->validate($rules,$_REQUEST(接受到的参数组));
```

