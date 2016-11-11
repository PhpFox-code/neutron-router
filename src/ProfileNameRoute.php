<?php
namespace Phpfox\Router;

use Phpfox\Mvc\App;

/**
 * Class ProfileNameRoute
 *
 * @package Phpfox\Router
 */
class ProfileNameRoute extends StandardRoute
{
    /**
     * @inheritdoc
     */
    protected function filter(Result $result)
    {
        $content = App::instance()->getEvent()
            ->trigger('onFilterProfileNameRun', $result, null);

        if (!$content) {
            return false;
        }

        $data = null;


        foreach ($content->getResponse() as $data) {
            break;
        }

        if (!is_array($data)) {
            return false;
        }

        if (empty($data['profileType'])) {
            return false;
        }

        if (empty($data['profileId'])) {
            return false;
        }

        $result->addVars($data);

//        var_dump($content);var_dump($result->getVars());exit;

        return true;
    }
}