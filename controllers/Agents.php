<?php namespace Mohsin\Txt\Controllers;

use Flash;
use Redirect;
use BackendMenu;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use ApplicationException;
use Mohsin\Txt\Models\Agent;
use Backend\Classes\Controller;

/**
 * Agents Back-end Controller
 */
class Agents extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Mohsin.Txt', 'txt', 'agents');
    }

    public function populate()
    {
    	$this->pageTitle = "Populate";
    	$this->addJs('/modules/backend/widgets/lists/assets/js/october.list.js', 'core');
    }

    public function onFetch()
    {
    	if( empty(trim(post('url'))) )
    		throw new ApplicationException('URL field is empty');
    	$client = new Client();
    	try {
			$response = $client->get(post('url'));
		} catch(RequestException $ex) {
			throw new ApplicationException('The resource appears to be unavailable at the specified URL');
		}
			$agents = explode("\n", $response->getBody());
    	$this -> vars['delimiter'] = post('delimiter');
    	$this -> vars['items'] = $agents;
    	return "done";
    }

    public function onClear()
    {
    	Agent::truncate();
    	return Redirect::to('backend/mohsin/txt/agents');
    }

    public function onAccept()
    {
      if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
        foreach ($checkedIds as $item) {
        		$item = explode(post('delimiter'), $item);
						$agent = new Agent;
						$agent->name = $item[0];
						$agent->comment = $item[1];
						if(Agent::whereName($item[0])->count() > 0)
							continue;
						$agent->save();
        }
        Flash::success('Successfully Added Items');
      }
    	return Redirect::to('backend/mohsin/txt/agents');
    }
}