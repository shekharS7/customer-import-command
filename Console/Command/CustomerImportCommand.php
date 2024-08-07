<?php
/**
 * Copyright © Shekhar Suman, 2024. All rights reserved.
 * See COPYING.txt for license details.
 * 
 * @package     AberrantCode_CustomerImport
 * @version     1.0.0
 * @license     MIT License (http://opensource.org/licenses/MIT)
 * @autor       Shekhar Suman
 */
namespace AberrantCode\CustomerImport\Console\Command;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use AberrantCode\CustomerImport\Model\CsvCustomerImport;
use AberrantCode\CustomerImport\Model\JsonCustomerImport;


class CustomerImportCommand extends Command
{
    protected static $defaultName = 'customer:import';

    protected $customerRepository;
    private $filesystem;
    private $csvCustomer;
    private $jsonCustomer;
    
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Filesystem $filesystem,
        CsvCustomerImport $csvCustomer,
        JsonCustomerImport $jsonCustomer
    ) {
        parent::__construct();
        $this->customerRepository = $customerRepository;
        $this->filesystem = $filesystem;
        $this->csvCustomer = $csvCustomer;
        $this->jsonCustomer = $jsonCustomer;
    }

    protected function configure()
    {
        $this->setName('customer:import')
             ->setDescription('Import customers from a CSV or JSON file')
             ->setHelp('This command allows you to import customers from a CSV or JSON file')
             ->addArgument('profile', InputArgument::REQUIRED, 'Profile name')
             ->addArgument('source', InputArgument::REQUIRED, 'Source file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profile = $input->getArgument('profile');
        $source = $input->getArgument('source');
        
        // Use pathinfo() function to get file extension
        $extension = pathinfo($source, PATHINFO_EXTENSION);

        // Validate profile
        if ($profile !=  'sample-csv' && $profile !=  'sample-json') {
            $output->writeln('<error>Invalid profile value</error>');
            return Command::FAILURE;
        }
         // Validate source
        if ($extension !=  'csv' && $extension != 'json') {
            $output->writeln('<error>Invalid source value</error>');
            return Command::FAILURE;
        }

        // Implement logic to parse the input file based on the profile
        // Extract customer data from the file
        // Import customers using customer repository
        try { 
            $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $filepath = $mediaDir->getAbsolutePath() . $source;
            if ($profile == 'sample-csv' && $extension == 'csv'){
                $this->csvCustomer->import($filepath, $output);
                $output->writeln('<info>Customers imported successfully.</info>');
                return Command::SUCCESS;
            } 
            if ($profile == 'sample-json' && $extension == 'json'){
                $this->jsonCustomer->import($filepath, $output);
                $output->writeln('<info>Customers imported successfully.</info>');
                return Command::SUCCESS;
            }
            return Command::SUCCESS;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $output->writeln("<error>$msg</error>", OutputInterface::OUTPUT_NORMAL);
            return Command::FAILURE;
        }
    }
}
