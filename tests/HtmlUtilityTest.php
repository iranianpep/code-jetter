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

    public function testGenerateCheckboxes()
    {
        $utility = new HtmlUtility();

        $ios = [
            [
                'i' => [
                    'options' => ['a']
                ],
                'o' => "<div><label><input type='checkbox' value='a'> a</label></div>"
            ],
            [
                'i' => [
                    'options' => ['a', 'b']
                ],
                'o' => "<div><label><input type='checkbox' value='a'> a</label></div><div><label><input type='checkbox' value='b'> b</label></div>"
            ]
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['o'], $utility->generateCheckboxes($io['i']['options']));
        }

        $ios = [
            [
                'i' => [
                    'options' => ['a', 'b'],
                    'name' => 'groups[]',
                    'checked' => ['a', 'b']
                ],
                'o' => "<div><label><input type='checkbox' name='groups[]' value='a' checked> a</label></div><div><label><input type='checkbox' name='groups[]' value='b' checked> b</label></div>"
            ]
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['o'], $utility->generateCheckboxes($io['i']['options'], $io['i']['name'], $io['i']['checked']));
        }
    }

    public function testGenerateRadioButtons()
    {
        $utility = new HtmlUtility();

        $ios = [
            [
                'i' => [
                    'options' => ['a']
                ],
                'o' => "<div><label><input type='radio' value='a'> a</label></div>"
            ],
            [
                'i' => [
                    'options' => ['a', 'b']
                ],
                'o' => "<div><label><input type='radio' value='a'> a</label></div><div><label><input type='radio' value='b'> b</label></div>"
            ]
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['o'], $utility->generateRadioButtons($io['i']['options']));
        }

        $ios = [
            [
                'i' => [
                    'options' => ['a', 'b'],
                    'name' => 'groups',
                    'checked' => 'b'
                ],
                'o' => "<div><label><input type='radio' name='groups' value='a'> a</label></div><div><label><input type='radio' name='groups' value='b' checked> b</label></div>"
            ]
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['o'], $utility->generateRadioButtons($io['i']['options'], $io['i']['name'], $io['i']['checked']));
        }
    }
}
