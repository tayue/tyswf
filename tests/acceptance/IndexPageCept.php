<?php use AppTest\AcceptanceTester;
use PHPUnit\Framework\Assert;
//测试用例1
/**
$I = new AcceptanceTester($scenario);
$I->wantTo('perform actions and see result');

$I->amOnPage('/');	//切换到配置站点 http://www.kkh86.com 的 / 页面,拼起来就是 http://www.kkh86.com/
$I->see('indexAction');	//断言可以在这个页面里看到指定的文字
 */

//测试用例2

$I = new AcceptanceTester($scenario);	//实例化一个测试者，将全局变量$scenario传进去作为构造参数
$I->wantTo('perform actions and see result');	//我想执行一些动作并且看看结果



$I->amOnPage('/');	//我在 / 这个页面
$I->see('hello world!');	//我看得到“我叫KK"这串文字
$I->click('content');	//我击带有“文章“这两个字的链接
$I->seeCurrentUrlEquals('/list.html');	//我看到当前网址是'/article/list.html'
$I->dontSee('我叫KK');	//我不想看到“我叫KK"这串文字





$I->seeElement('.pArcList');	//我看到class="pArcList"的一个元素
$buttonText = $I->grabTextFrom('div.pArcList div.divArea #span');	//我通过 nav li:nth-child(3) a 这个CSS选择器定位到一个元素并捕捉它里面的文本
Assert::assertEquals('Button', $buttonText);	//调用断言模块断言变量
//$I->click('nav li:nth-child(55) a');
//$I->dontSeeCurrentUrlEquals('/article/list.html');	//我不想看到当前的网址是'/article/list.html'

$I->seeInTitle('Http Server sssss !!!');	//我能在title里看到'Http Server  sssss !!!'三个字
//
//
$I->fillField('email', 'xxx@yy.com');
$I->fillField('password', '121212');
$I->click('#submitButton');	//我击带有“文章“这两个字的链接
$I->see('Register Success !');		//无选择器,将直接在整个页面查找文本
//
////ajax
$param = [
    'page' => 2,	//假设我们要第2页的数据
    'type' => 3,	//假设数据有类型~
];	//这个$param是要异步请求时提交上去的参数
$I->sendAjaxRequest('get', 'http://192.168.99.88:9501/home/test/ajax',$param);	//这样其实就相当于 /datalist.php?page=2&type=3
$I->seeResponseCodeIs(200);	//断言请求后,服务端响应回来的报文状态码应该是200
////ajax 请求测试 $m['PhpBrowser']; //这样来取出一个模块
$modules=$I->getModules();
$browser = $modules['PhpBrowser']; //这样来取出一个模块
$jsonString = $browser->client->getInternalResponse()->getContent(); //通过模块获取响应正文,就是那串json,但必须转成string(注意我代码后面有toString的调用),否则你会得到一个对象,这框架抽象得挺厉害,连个响应报文内容都是对象
$jsonArray = json_decode($jsonString, true);
codecept_debug($jsonArray);
Assert::assertIsArray($jsonArray);	//断言解码后的类型
Assert::assertEquals(2, count($jsonArray));	//断言数据个数


//$I->grabFromDatabase('book', '*', ['id' => $testBookId]);	//从数据库取出书籍记录
//$I->updateInDatabase('users', array('isAdmin' => true), array('email' => 'miles@davis.com'));
//$jsonString = $browser->client->getInternalResponse()->getContent()->__toString(); //通过模块获取响应正文,就是那串json,但必须转成string(注意我代码后面有toString的调用),否则你会得到一个对象,这框架抽象得挺厉害,连个响应报文内容都是对象
//codecept_debug($jsonString);
//$jsonArray = json_decode($jsonString, true);
//Assert::assertInternalType('array', $jsonArray);	//断言解码后的类型
//codecept_debug($jsonArray);
//Assert::assertEquals(2, count($jsonArray));	//断言数据个数


//$I->see('登陆', '#mainDiv form');		//在选择器所指的区域里开始查找文本
//$I->see('登陆', '#mainDiv form button');		//更加精确了
//
//$I->dontSee('登陆', '#mainDiv form');		//指定区域里排除文本,就是断言这个区域里不会有这个文本咯
//
//$I->click('忘记密码');	//点击带有这"登陆"两个字的a标签
//$I->click('忘记密码', '#mainDiv .loginForm');	//在指定区域开始查找这个文字的链接
//$I->click('//form/*[@type=submit]');	//通过XPath定位到一个submit按钮并点击它,实现表单提交

//
//$I->amOnPage('/about.html');	//我在'/about.html'这个页面
//$I->seeNumberOfElements('.wrapTabContent', 3);	//我看到3个class="wrapTabContent"的元素
//
//$I->amOnPage('/xxxxx.html');	//我在 '/xxxxx.html' 这个页面
//$I->seePageNotFound();	//我看到页面不存在的错误（根据返回状态码是否404判断）

/**
    接下来我主要介绍几个可能经常用得到的方法,大家可以参考这几个方法去理解其它类似的方法,毕竟不是全部人都能看懂官方那个类里面自带的英文注解的~

    amOnPage($url) 将当前页面切换到指定的URL,这个url可以是完整的URL也可以是一个相对URL

    amOnUrl($url) 切换当前基础网址,本身我们 $I->amOnPage('/index.html') 的话会默认相对于yml配置里的URL的嘛,现在如果通过这个方法切换成别的URL,则后面所有 amOnPage 方法的相对URL都是相对于这个新的URL,可以说是动态修改那个URL配置了,仅限该测试用例的当前会话生效,运行结束后下一个测试用例无效

    see($text, $selector = null) 断言页面上会存在$text这个参数的字符,如果指定$selector的话则会在$selector里面查找这个字符

    canSee($text, $selector = null) 跟see方法一样是查找文本的,但是如果找不到文本却不会停止运行,还有其它很多can开头的方法名称,都是断言失败不停止的方法,但失败会产生在报告里

    click($link, $context = null) 点击一个链接,这样会导致当前页面变更哦

    而$link参数可以是a标签里的文字,也可以是选择器

    但是其实如果button的name或value的值符合$link的话都会被定位到哦

    对于img标签则会把alt属性也加入匹配定位的内容中

    最后呢如果匹配到的是一个type=submit的button的话则会同时触发表单的提交

    fillField($field, $value) 向name="$field"这个表单项填充$value这个值

    比如你有一个注册页,用select控件来选择性别,value=2就是女性

    $I->fillField('sex', 2);		//选择女性
    seeInField($field, $value) 断言name="$field"的表单项的value值与$value是匹配的

    接上面fillField的例子,默认性别是未知,value是0,做一个表单修改的测试

    $I->seeInField('sex', 3);	//断言默认是3
    $I->fillField('sex', 2);		//填充2
    $I->seeInField('sex', 2);	//断言填充后就是2,但实际是填充后再断言,在基础的验收测试里意义不大,用在后面的WebDriver验收测试中才最能彰显测试效果

    $I->amOnPage('/user-center.html');	//假如切换到注册页面
    $I->seeInField('username', '请填写用户名');	//断言用户名的默认值
    seeCheckboxIsChecked($checkbox) 断言$checkbox所指的勾选项是已经勾选了的($checkbox也是一个选择器!个人觉得该参数应该命名为$selector)

    然后如果要断言是未勾选的就是用dontSeeCheckboxIsChecked($checkbox)

    submitForm($selector, $params, $button = null) 将$selector选中的表单发起提交,$params是key => value表达的表单参数值,这样你就不需要慢慢用 $I->fillField 这些方法来填充表单而是在这里直接传递参数了, $button 是提交按钮的选择器,可以不填,但如果存在 修改/删除 等多个提交按钮时就需要用 $button 了

    seeCookie($name) 断言存在指定$name的Cookie

    resetCookie($name) 删除cookie

    断言没有指定Cookie的话当然也是用dontSeeCookie($name)了,其实好多see方法都有一个对应的dontSee方法和canSee方法

    canSeePageNotFound 断言当前是404页面,之前的例子里你有见过,我这里不举例了

    grabValueFrom($field) 获取HTML中name="$field"这个表单项的值,但这里要注意,这个表单项必须要被form标签包起来哦,我试过有一次对一个select标签的值死活取不出来,后来发现form里的能拿出来,于是才注意到有这个坑...

    而关于这个方法的使用嘛,我要举一下例子,为什么呢?因为验收测试中默认是没有assert系列的断言方法的!你想想喔,如果你想测试的页面是一个注册页,用select控件来选择性别,默认是未知,value是3,然后你想测试时确认这个值默认是3怎么办?我认为只能这样:先通过grabValueFrom方法将表单值获取出来,再用断言方法断言这个值,代码如下:

    //顶上要 use \Codeception\Module\Asserts;

    $gender = $I->grabValueFrom('sex');
    Asserts::assertEquals(3, $gender);
    这样来断言,其实之前的例子有刻意添加过这个演示代码,具体嘛,将会在 验收测试 - 扩展 章节中解释,反正如果你要断言的话就要引用Asserts,然后再通过静态方法来调用断言方法,它的断言方法和PHPUnit差不多

    amOnSubdomain($subdomain) 切换到子域名,比如配置时URL是 qq.com,或者 www.qq.com, 又或者是 shop.qq.com ,执行

    $I->amOnSubdomain('pay');
    则是意味着

    $I->amOnPage('/xxx.html');
    会切换到 http://pay.qq.com/xxx.html

    sendAjaxRequest($method, $uri, $params = null):发送一个ajax请求,$method是请求的方式,比如get,post,delete,put,也可以自定义请求方式,具体看服务端程序是否扩展了这个请求方式并作响应了

    sendAjaxGetRequest($uri, $params = null)用get方法发送一个ajax请求,跟sendAjaxRequest('get', ...)是一样的

    sendAjaxPostRequest($uri, $params = null)用get方法发送一个ajax请求,跟sendAjaxRequest('post', ...)是一样的

    seeResponseCodeIs($code) 断言上次发生HTTP请求后的响应状态码

    setHeader($header, $value) 设置下一次请求的header

    attachFile($field, $filename) 附加一个文件,以$field这个字符串变量来命名,比如$field叫image的话,就是PHP角度访问的那个$_FILES['image']了; 而$filename就是附加的文件是哪个文件,是你当前测试用例所在磁盘上的物理路径哦

    amHttpAuthenticated($username, $password) 用指定的用户名和密码提交到当前的Http认证中,认证不通过将导致断言失败

    就写到这了,我还真写不完整个AcceptanceTester的断言,什么时候闲得蛋疼了,或哪位朋友有空义务帮忙写写再说吧~~帮忙写的话用markdown哦^-^
 *
 */
