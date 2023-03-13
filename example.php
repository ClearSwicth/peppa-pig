<?php

$loader = require_once __DIR__ . '/vendor/autoload.php';

use Clearswitch\PeppaPigCli\Console;

//无样式的输出
Console::outPut("Hello Word");

//添加样式输出
Console::outPut(Console::setStyle("Hello Word", 'blue'));

//数据拼接
Console::outPut(implode(" ", [
    Console::setStyle(date("Y-m-d H:i:s"), 'blue'),
    Console::setStyle("Hello Word", 'blue')
]));

//进度条
for ($i = 0; $i <= 100; $i += 10) {
    Console::process($i, 100, 50, "#");
    sleep(1);
}

//表格
$data = [
    ["刘骚骚", "是个", "超级大可爱",],
    ["姓名1", "啥", "怎么回事1"]
];
$header = ["姓名", "啥", "怎么回事"];
Console::table($data, $header, 'blue');
//
$data=[
    [
        Console::setStyle("刘骚骚",'red'),
        Console::setStyle("刘骚骚",'red','bg_green'),
    ]
];
$header = ["姓名", "啥", "怎么回事"];
Console::table($data, $header);
