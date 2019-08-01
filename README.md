# Hyperf RPC

## 简介

这个项目用于改造 hyperf jsonrpc 服务，实现代理类调用接口，支持自定义函数参数类型和返回值类型，以及异常。

这里使用 CalculatorService 为例子， `app/Service/CalculatorServiceInterface.php`
中方法：

```php
<?php

namespace App\Service;

interface CalculatorServiceInterface
{
    /**
     * @param Integer[] $a
     * @return Integer
     */
    public function squareSum(array $a): Integer;

    /**
     * @param int $a
     * @param int $divider
     * @return float
     * @throws \InvalidArgumentException
     */
    public function divide(int $a, int $divider);
}
```

`squareSum` 方法参数及返回值均为自定义类型，而 `divide` 函数当参数 `$divider` 为0 时将抛出 `\InvalidArgumentException` 。

首先启动服务：

```bash
php ./bin/hyperf.php start
```

运行 `php ./bin/hyperf.php test-rpc` 测试 rpc 服务：

```
 input::
 > 3^2 + 4^2

3^2 + 4^2 = 25

 input::
 > 3/0

InvalidArgumentException: Expected non-zero value for divider
```

