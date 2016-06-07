# Mappers
- [Overview](#overview)
- [Getting Data](#getting-data)

<a name="overview"></a>
## Overview
Mappers are responsible to fetch data from the database, map it to the relevant model and return objects. A mapper is named with `Mapper` suffix e.g. `UserMapper` and is located in `mappers` folder of its component.  Also every mapper class must extend `BaseMapper` abstract class.

<a name="getting-data"></a>
## Getting Data
This is a function for `UserMapper` class:
```
/**
 * @param      $username
 * @param null $status
 * @param bool $excludeArchived
 *
 * @return Output
 * @throws \Exception
 */
public function getOneByUsername($username, $status = null, $excludeArchived = true)
{
    /**
     * start validating
     */
    $output = new Output();
    try {
        $requiredRule = new ValidatorRule('required');

        $usernameInput = new Input('username', [$requiredRule]);

        $validatorOutput = (new Validator([$usernameInput], ['username' => $username]))->validate();

        if ($validatorOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessages($validatorOutput->getMessages());
            return $output;
        }
    } catch (\Exception $e) {
        (new \CodeJetter\core\ErrorHandler())->logError($e);
    }
    /**
     * finish validating
     */

    $criteria = [
        [
            'column' => 'username',
            'value' => $username
        ]
    ];

    if ($status !== null && is_numeric($status)) {
        $criteria[] = [
            'column' => 'status',
            'value' => $status
        ];
    }

    try {
        $result = $this->getOne($criteria, [], $excludeArchived);
        if (!empty($result)) {
            $output->setSuccess(true);
            $output->setData($result);
        } else {
            $output->setSuccess(false);
        }

        return $output;
    } catch (\PDOException $e) {
        (new \CodeJetter\core\ErrorHandler())->logError($e);
    }
}
```

As you see input validation is implemented in the beginning of the function. Then `criteria` array which is used to generate `select` query is passed to `getOne` function which is in `BaseMapper` class. In this case `result` is an object which is set in `data` property of `output` object.