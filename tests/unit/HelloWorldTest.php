<?php namespace AppTest;

/**
    这里提供一些通常我们会用得到的断言列表,其实也是我从网上收集整理下来的，但你不要全部记下，而是你要用到这相关的判断时才来查这个字典就好了。

    这些方法后面的 $message 参数就是前面提到的断言失败的消息了，有的没有参数说明或比较含糊，可以自行用关键词phpunit assertXXXX上网搜索一下，但必要用上的都有清楚说明。

    assertFalse(bool $condition, string $message = '') : 断言 $condition 的结果为 false，assertTrue 与之相反。

    assertInternalType($expected, $actual, string $message = '') : 断言变量类型为 $expected，相当于 is_string、is_bool 和 is_numeric 等类型判断，例：

    $this->assertInternalType('string', $var);
    $this->assertInternalType('numeric', $var);
    $this->assertInternalType('bool', $var);
    $this->assertInternalType('bool', $var);
    assertNotInternalType($expected, $actual, string $message = '') : 与上一条相反，断言变量的类型不为 $expected，例：

    $this->assertInternalType('string', $var)	//$var的类型不为string
    assertEquals(mixed $expected, mixed $actual, string $message = '') : 断言 $actual 与 $expected 相同，类似 == 比较，例：

    $this->assertEquals(5, $age)
    $this->assertEquals($obj1, $obj2)
    assertNotEquals() : 与上条相反,类似于 !=

    assertInstanceOf($expected, $actual, string $message = '') : 断言 $actual 为 $expected 的实例，相当于 instanceof 关键字判断，例：

    $this->assertInstanceOf('common\model\Article', $model)
    assertEmpty(mixed $actual, string $message = '') : 断言 $actual 变量为空，相当于 empty。

    assertNotEmpty($variable, string $message = '') : 断言 $variable 变量不为空，相当于 !empty。

    assertNull(mixed $variable, string $message = '') : 断言 $variable 的值为 null，相当于 is_null。

    assertNotNull() : 与上条相反。

    assertArrayHasKey(mixed $key, array $array, string $message = '') : 断言数组 $array 含有索引 $key, 相当于 isset 或者 array_key_exists。

    assertGreaterThan(mixed $expected, mixed $actual, string $message = '') : 断言 $actual 比 $expected 大，相当于 > 号比较。

    assertGreaterThanOrEqual(mixed $expected, mixed $actual, string $message = '') : 断言 $actual 大于等于 $expected，相当于 >=。

    assertAttributeGreaterThan() : 同上,只是用于断言类的属性。

    assertAttributeInternalType() and assertAttributeNotInternalType() : 断言类属性用。

    assertRegExp(string $pattern, string $string, string $message = '') : 断言字符串 $string 符合正则表达式 $pattern，相当于 preg_match。

    assertNotRegExp() : 与上条相反

    assertLessThan(mixed $expected, mixed $actual, string $message = '') : 断言 $actual 小于 $expected，相当于 < 号比较。

    assertAttributeLessThan() : 断言类属性小于$expected

    assertLessThanOrEqual(mixed $expected, mixed $actual, string $message = '') : 断言 $actual 小于等于 $expected，相当于 <=。

    assertAttributeLessThanOrEqual() : 断言类属性小于等于 $expected。

    assertAttributeGreaterThanOrEqual() : 断言类的属性。

    assertObjectHasAttribute(string $attributeName, object $object, string $message = '') : 断言 $object 含有属性 $attributeName，相当于 isset($obj->attr)。

    assertObjectNotHasAttribute(…) : 与上条相反

    assertContainsOnly(string $type, Iterator|array $haystack, boolean $isNativeType = NULL, string $message = '') : 断言迭代器对象/数组 $haystack 中只有 $type 类型的值, $isNativeType 设定为 PHP 原生类型，$message同上，相当于遍历一个数组再判断每一个元素的类型，例：

    $this->assertContainsOnly('string', $userNames);	//断言一个所谓用户名称集合的数组中全部item都是字符串类型
    assertContains(mixed $needle, Iterator|array $haystack, string $message = '') : 断言迭代器对象$haystack/数组$haystack含有$needle ，相当于in_array，相当于array_search或者in_array

    assertNotContains(mixed $needle, Iterator|array $haystack, string $message = '') : 与上条相反。

    assertAttributeEquals($actual, $expected) 以及 assertAttributeNotEquals($actual, $expected) : 断言类属性名称$actual的值是否与 $expected 相同/不同。

    assertClassHasAttribute(string $attributeName, string $className, string $message = '') : 断言类 $className 含有属性 $attributeName，例：

    $this->assertClassHasAttribute('name', 'app\role\User', 'User类没有name属性')
    assertClassHasStaticAttribute(string $attributeName, string $className, string $message = '') : 断言类 $className 含有静态属性 $attributeName。

    assertFileEquals(string $expected, string $actual, string $message = '') : 断言文件 $actual 和 $expected 所指的数据类型相同，例：

    $this->assertFileEquals('jpeg', '/www/web/1.jpg')
    assertFileExists(string $filename, string $message = '') : 断言文件 $filename 存在。

    assertFileNotExists() : 与上条相反。

    assertStringEqualsFile(string $expectedFile, string $actualString, string $message = '') : 断言 $actualString 包含在文件 $expectedFile 的内容中，例：

    $this->assertFileEquals('E:\1.log', 'db_error')
    assertStringNotEqualsFile() : 与上条相反。

    assertStringStartsWith(string $prefix, string $string, string $message = '') : 断言 $string 的开头为 $suffix，例：

    $this->assertStringStartsWith('match', Url::to(['match/showHome']))	//断言生成的URL是以match开头的
    assertStringStartsNotWith() : 与上条相反。

    assertStringEndsWith(string $suffix, string $string, string $message = '') : 断言 $string 的末尾为 $suffix 结束。

    assertStringEndsNotWith() : 与上条相反。

    以上断言无法满足测试代码的判断时，上网搜索关键词phpunit断言大全即可。
 */

class HelloWorldTest extends \Codeception\Test\Unit
{
    /**
     * @var \AppTest\UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {

    }

    public function testM1(){
       // $this->tester->grabColumnFromDatabase('users', 'email', ['id' => 1]);
        $userData = $this->tester->grabColumnFromDatabase('user', 'age', ['id' => 1]);	//从数据库取出书籍记录
        codecept_debug($userData);
       // $this->tester->updateInDatabase('user', array('age' => 55), array('email' => 'miles@davis.com'));
       // $this->tester->assertInternalType("ss");
        $urlInfo = parse_url('http://aa.com/bb/cc/dd.html');
//        //在测试用例里调用
        $this->tester->assertArrayHasKeys('scheme,host,path', $urlInfo);
//        $this->assertTrue(true);	//传的参数不是true，断言失败
    }
}