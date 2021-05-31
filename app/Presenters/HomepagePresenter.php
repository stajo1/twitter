<?php declare(strict_types=1);

namespace App\Presenters;
use App\Controls\Twitter\ITwitterFactory;
use App\Controls\Twitter\Twitter;
use Nette;
use Tracy\Debugger;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    /** @var ITwitterFactory @inject */
    public $twitterFactory;


    public function createComponentTwitter(): Twitter
    {
        $control = $this->twitterFactory->create();
        return $control;
    }
}
