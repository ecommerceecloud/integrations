<?php

namespace Ecloud\Integrations\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use \Magento\Framework\App\State;
use Ecloud\Integrations\Helper\Data;
use \Ecloud\Integrations\Model\Export as ExportModel;

class Export extends Command
{
	/**
	 * @var Data
	 */
	protected $_helper;

	/**
	 * @var ExportModel
	 */
	protected $_exportModel;

	/**
	 * @var State
	 */
	protected $_state = null;

	const TYPE_PARAM_NAME = 'type';
	const ENTITY_ID_PARAM_NAME = 'id';
	const LOG_NAME = "EXPORT_COMMAND";

	/**
	 * @param ExportModel $exportModel
	 * @param State $state
	 * @param Data $helper
	 */
	public function __construct(
		ExportModel $exportModel,
		State $state,
		Data $helper
	) {
		$this->_state = $state;
		$this->_exportModel = $exportModel;
		$this->_helper = $helper;
		parent::__construct();
	}

	protected function configure()
	{
		$options = [
			new InputOption(
				self::TYPE_PARAM_NAME,
				null,
				InputOption::VALUE_REQUIRED,
				'Type'
			),
			new InputOption(
				self::ENTITY_ID_PARAM_NAME,
				null,
				InputOption::VALUE_IS_ARRAY + InputOption::VALUE_OPTIONAL,
				'Order ID or IDs (optional)',
				null
			),
		];

		$this->setName('ecloud:integrations:export')
			->setDescription('Export to ERP using Ecloud Integrations module')
			->setDefinition($options);

		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$integrationName = $input->getOption(self::TYPE_PARAM_NAME);
		$entityIds = $input->getOption(self::ENTITY_ID_PARAM_NAME);

		$this->_state->setAreaCode('adminhtml');
		try {
			$this->_exportModel->start($integrationName, $entityIds);
		} catch (\Exception $e) {
			$this->_helper->log('Error starting export. ' . $e->getMessage(), self::LOG_NAME);
			throw new \Exception('Error starting export. ' . $e->getMessage());
		}
	}
}
