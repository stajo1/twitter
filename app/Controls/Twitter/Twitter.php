<?php declare(strict_types=1);

namespace App\Controls\Twitter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Tracy\Debugger;

class Twitter extends Control
{
    const URL = 'https://api.twitter.com/1.1/search/tweets.json';
    const DEFAULT_REQUEST_COUNT = 100;
    const DEFAULT_TEMPLATE = __DIR__ . '/default.latte';

    /** @var Request @inject */
    public $request;

    /** @var array */
    private $config;

    /** @var int */
    private $requestCount = 100;


    public function __construct(array $config)
    {
        $this->config = $config;
        $this->requestCount = $config['request_count']  ??  self::DEFAULT_REQUEST_COUNT;
    }


    public function render()
    {
        $this->template->setParameters([
            'twitts' => $this->read()
        ]);
        $this->template->render(self::DEFAULT_TEMPLATE);
    }


    private function read(): string
    {
        $query = $this->getQuery();
        $searchString = "?q={$query}&count={$this->requestCount}&result_type=recent";
        $twitter = new \TwitterAPIExchange($this->config);

        try {
            $result = $twitter->setGetfield($searchString)
                ->buildOauth(self::URL, 'GET')
                ->performRequest();

            $twitts = json_decode($result, true);
            $twitts = $twitts['statuses']  ??  [];
            $twitts = array_column($twitts, 'text');
            $twitts = json_encode($twitts);

        } catch (\Exception $exception) {
            Debugger::dump('Error: ' . $exception->getMessage());
            $this->flashMessage('Page is not working');
        }


        return $twitts  ??  '';
    }


    private function getQuery(): string
    {
        $search = $this->request->getPost('search') ?? null;
        $search = str_replace(',', ' OR ', $search);
        return $search;
    }


    public function createComponentSearchForm()
    {
        $form = new Form();
        $form->addText('search', 'Search')->setRequired('Enter input Search!')
            ->setHtmlAttribute('placeholder', 'Separate with commas');
        $form->addSubmit('submit');
        return $form;
    }

}


interface ITwitterFactory
{

    public function create(): Twitter;
}
