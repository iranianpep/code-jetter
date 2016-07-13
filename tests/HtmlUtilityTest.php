<?php


namespace CodeJetter\tests;


use CodeJetter\core\utility\HtmlUtility;

class HtmlUtilityTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateDropDownList()
    {
        $utility = new HtmlUtility();

        $ios = [
            [
                'i' => [
                    'options' => ['a']
                ],
                'o' => "<select><option value='a'>a</option></select>"
            ],
            [
                'i' => [
                    'options' => ['a', 'b']
                ],
                'o' => "<select><option value='a'>a</option><option value='b'>b</option></select>"
            ],
            [
                'i' => [
                    'options' => []
                ],
                'o' => ''
            ]
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['o'], $utility->generateDropDownList($io['i']['options']));
        }

        $ios = [
            [
                'i' => [
                    'options' => ['a', 'b'],
                    'name' => 'statuses',
                    'selected' => 'b',
                    'configs' => [
                        'titleMapper' => [
                            'a' => 'Title A',
                            'b' => 'Title B'
                        ]
                    ]
                ],
                'o' => "<select name='statuses'><option value='a'>Title A</option><option value='b' selected>Title B</option></select>"
            ],
            [
                'i' => [
                    'options' => ['a', 'b'],
                    'name' => 'statuses',
                    'selected' => 'b',
                    'configs' => [
                        'titleMapper' => [
                            'a' => 'Title A'
                        ]
                    ]
                ],
                'o' => "<select name='statuses'><option value='a'>Title A</option><option value='b' selected>b</option></select>"
            ],
            [
                'i' => [
                    'options' => ['a', 'b'],
                    'name' => 'statuses',
                    'selected' => 'b',
                    'configs' => [
                        'titleMapper' => 'key'
                    ]
                ],
                'o' => "<select name='statuses'><option value='a'>0</option><option value='b' selected>1</option></select>"
            ],
            [
                'i' => [
                    'options' => ['a'],
                    'name' => 'statuses',
                    'selected' => 'b',
                    'configs' => [
                        'titleMapper' => 'key'
                    ]
                ],
                'o' => "<select name='statuses'><option value='a'>0</option></select>"
            ]
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['o'], $utility->generateDropDownList($io['i']['options'], $io['i']['name'], $io['i']['selected'], $io['i']['configs']));
        }
    }
}
