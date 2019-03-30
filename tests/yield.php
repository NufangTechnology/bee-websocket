<?php
function test_1()
{
    $data = [1, 2,3,4];

    foreach ($data as $item) {
        yield $item;
    }
}

/**
 * @return Generator
 */
function test_2()
{
    return test_1();
}

function test_3()
{
    foreach (test_2() as $item) {
        var_dump($item);
    }
}

test_3();
