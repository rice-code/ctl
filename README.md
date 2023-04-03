[![License](https://img.shields.io/badge/license-Apache%202-4EB1BA.svg)](https://www.apache.org/licenses/LICENSE-2.0.html)
[![github star](https://img.shields.io/github/stars/rice-code/ctl.svg)]('https://github.com/dmf-code/basic/stargazers')
[![github fork](https://img.shields.io/github/forks/rice-code/ctl.svg)]('https://github.com/dmf-code/basic/members')
[![.github/workflows/ci.yml](https://github.com/rice-code/ctl/actions/workflows/ci.yml/badge.svg)](https://github.com/rice-code/ctl/actions/workflows/ci.yml)

## php 命令行 （php control）

### 安装

```shell script
composer require rice/ctl
```

### 功能点
1. setting, getting 注释生成命令 [锚点](#访问器自动生成注释)
2. json 转 class 对象命令 [锚点](#json-转-class-对象)
3. 多语言国际化（i18n） [锚点](#i18n-缓存生成)


### 访问器自动生成注释

以这个 `tests\Support\Annotation\Cat.php` 文件为例，我们使用了 `Accessor` 这个 `trait`。所以会
存在 `setxxx()` 和 `getxxx()`，但是这里面会造成实例化类后调用没有相关的函数提示。为了解决这个问题，可以
使用 `php ctl.php rice:accessor xxx\tests\Support\Annotation\Cat.php` 去执行自动生成注释。

> 只会生成protected 属性的注释，如果属性没有指定类型，那么会查看注释是否有 @var 指定相关类型，有的
> 话自动获取

生成前：
```php
class Cat
{
    use AutoFillProperties;
    use Accessor;

    /**
     * 眼睛.
     *
     * @return $this
     *
     * @throws \Exception
     *
     * @var string
     * @Param $class
     */
    protected $eyes;

    /**
     * @var S
     */
    protected $speak;
}
```

生成后：
```php
/**
 * Class Cat.
 * @method self     setEyes(string $value)
 * @method string   getEyes()
 * @method self     setSpeak(S $value)
 * @method S        getSpeak()
 */
class Cat
{
    use AutoFillProperties;
    use Accessor;

    /**
     * @var string
     * @Param $class
     */
    protected $eyes;

    /**
     * @var S
     */
    protected $speak;
}

```

#### tips：推荐属性是对象时不要使用长链式调用

##### bad

```php
$cat = new \Tests\Entity\Cat();
$cat->getSpeak()->text();
```

##### better
Cat重写一个方法
```php
public function getSpeakText(): string
{
    return $this->getSpeak()->text();
}

$cat->getSpeakText();
```

这样子做的好处是提高内聚性，虽然直接链式调用会方便使用，但是出现链式的一个
环节要修改名称的时候，如果多个地方都有使用到，那么修改起来就会存在多个地方。
重写方法后，统一使用 `Cat` 类的 `getSpeakText` 方法。需要修改时，就只
改动 `Cat` 类就行了，降低出错成本。

### json 转 class 对象

`_class_name`: 类名称
`_type`: 类的类型（DTO 或 Entity）
`_namespace`: 类的命名空间

调用 `php generator.php rice:json_to_class xxx\basic\tests\Generate\tsconfig.json xxx\basic\tests\Generate\`

第一个参数是输入的 `json` 文件路径，第二个参数是生成文件所在的目录

```json
{
  "_class_name": "Test",
  "_type": "Entity",
  "_namespace": "Tests\\Generate",
  "data": [
    {
      "insights": {
        "data": [
          {
            "name": "post_impressions",
            "period": "lifetime",
            "values": [
              {
                "value": 614
              }
            ],
            "title": "Lifetime Post Total Impressions",
            "description": "Lifetime: The number of times your Page's post entered a person's screen. Posts include statuses, photos, links, videos and more. (Total Count)"
          }
        ],
        "paging": {
          "previous": "xxxxxxxxxxxxxxx",
          "next": "yyyyyyyyyyyyyyy"
        }
      },
      "created_time": "2021-10-13T16:11:55+0000",
      "message": "Very important message"
    }
  ],
  "paging": {
    "cursors": {
      "before": "xxxxxxxxxxxxxxx",
      "after": "yyyyyyyyyyyyyyy"
    },
    "next": "zzzzzzzzzz"
  }
}
```

### i18n 缓存生成

```php
interface Other
{
    /**
     * @en one
     * @zh-CN 一
     */
    public const A = '1';
}

class TestEnum implements Other
{
    /**
     * @default OK
     */
    public const OK = '00000';
}
```
抛弃传统的单独配置多语言的描述，因为维护起来非常费劲。在新功能添加新的字段时，多语言
要在多个文件中进行映射，使用该形式可以让描述与属性绑定在一起，更加清晰。

```shell
php .\ctl.php i18n:cache xxx\ctl\tests\Enum xxx\ctl\tests\Lang Tests\Enum
```
第一个路径是需要生成缓存的目录，第二个是缓存输出的目录，第三个是生成缓存的命名空间前缀

