<?php

namespace Ecloud\Integrations\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use \Magento\Framework\App\State;
use Ecloud\Integrations\Helper\Data;
use \Ecloud\Integrations\Model\Import as ImportModel;

class Import extends Command
{
	/**
	 * @var Data
	 */
	protected $_helper;

	/**
	 * @var ImportModel
	 */
	protected $_importModel;

	/**
	 * @var State
	 */
	protected $_state = null;

	const INTEGRATION_NAME_PARAM_NAME = 'type';
	const RUN_TYPE_PARAM_NAME = 'run_type';
	const LOG_NAME = "IMPORT_COMMAND";

	/**
	 * @param ImportModel $importModel
	 * @param State $state
	 * @param Data $helper
	 */
	public function __construct(
		ImportModel $importModel,
		State $state,
		Data $helper
	) {
		$this->_state = $state;
		$this->_importModel = $importModel;
		$this->_helper = $helper;
		parent::__construct();
	}

	protected function configure()
	{
		$options = [
			new InputOption(
				self::INTEGRATION_NAME_PARAM_NAME,
				null,
				InputOption::VALUE_REQUIRED,
				'Integration name'
			),
			new InputOption(
				self::RUN_TYPE_PARAM_NAME,
				null,
				InputOption::VALUE_REQUIRED,
				"Run type (" . ImportModel::RUN_TYPE_FULL_IMPORT . "|" . ImportModel::RUN_TYPE_GENERATE_FILE . ")",
				ImportModel::RUN_TYPE_FULL_IMPORT
			)
		];

		$this->setName('ecloud:integrations:import')
			->setDescription('Import from ERP using Ecloud Integrations module')
			->setDefinition($options);

		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$integrationName = $input->getOption(self::INTEGRATION_NAME_PARAM_NAME);
		$runType = $input->getOption(self::RUN_TYPE_PARAM_NAME);

		$this->_state->setAreaCode('adminhtml');
		try {
			$this->_importModel->start($integrationName, $runType);
		} catch (\Exception $e) {
			$this->_helper->log('Error starting import. ' . $e->getMessage(), self::LOG_NAME);
			throw new \Exception('Error starting import. ' . $e->getMessage());
		}
	}
}
