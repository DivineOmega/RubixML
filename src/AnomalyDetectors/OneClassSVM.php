<?php

namespace Rubix\ML\AnomalyDetectors;

use Rubix\ML\Learner;
use Rubix\ML\DataType;
use Rubix\ML\Estimator;
use Rubix\ML\EstimatorType;
use Rubix\ML\Kernels\SVM\RBF;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Kernels\SVM\Kernel;
use Rubix\ML\Other\Traits\PredictsSingle;
use Rubix\ML\Specifications\DatasetIsNotEmpty;
use Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use InvalidArgumentException;
use RuntimeException;
use svmmodel;
use svm;

/**
 * One Class SVM
 *
 * An unsupervised Support Vector Machine (SVM) used for anomaly detection. The One
 * Class SVM aims to find a maximum margin between a set of data points and the
 * *origin*, rather than between classes such as with SVC.
 *
 * > **Note:** This estimator requires the SVM extension which uses the libsvm engine
 * under the hood.
 *
 * References:
 * [1] C. Chang et al. (2011). LIBSVM: A library for support vector
 * machines.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class OneClassSVM implements Estimator, Learner
{
    use PredictsSingle;
    
    /**
     * The support vector machine instance.
     *
     * @var \svm
     */
    protected $svm;

    /**
     * The hyper-parameters of the model.
     *
     * @var mixed[]
     */
    protected $params;

    /**
     * The trained model instance.
     *
     * @var \svmmodel|null
     */
    protected $model;

    /**
     * @param float $nu
     * @param \Rubix\ML\Kernels\SVM\Kernel|null $kernel
     * @param bool $shrinking
     * @param float $tolerance
     * @param float $cacheSize
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct(
        float $nu = 0.5,
        ?Kernel $kernel = null,
        bool $shrinking = true,
        float $tolerance = 1e-3,
        float $cacheSize = 100.0
    ) {
        if (!extension_loaded('svm')) {
            throw new RuntimeException('SVM extension not loaded'
                . ',  check PHP configuration.');
        }

        if ($nu < 0.0 or $nu > 1.0) {
            throw new InvalidArgumentException('Nu must be between'
                . "0 and 1, $nu given.");
        }

        $kernel = $kernel ?? new RBF();

        if ($tolerance < 0.0) {
            throw new InvalidArgumentException('Tolerance must be,'
                . " greater than 0, $tolerance given.");
        }

        if ($cacheSize <= 0.0) {
            throw new InvalidArgumentException('Cache size must be'
                . " greater than 0M, {$cacheSize}M given.");
        }

        $options = [
            svm::OPT_TYPE => svm::ONE_CLASS,
            svm::OPT_NU => $nu,
            svm::OPT_SHRINKING => $shrinking,
            svm::OPT_EPS => $tolerance,
            svm::OPT_CACHE_SIZE => $cacheSize,
        ];

        $options += $kernel->options();

        $svm = new svm();

        $svm->setOptions($options);

        $this->svm = $svm;

        $this->params = [
            'nu' => $nu,
            'kernel' => $kernel,
            'shrinking' => $shrinking,
            'tolerance' => $tolerance,
            'cache_size' => $cacheSize,
        ];
    }

    /**
     * Return the estimator type.
     *
     * @return \Rubix\ML\EstimatorType
     */
    public function type() : EstimatorType
    {
        return EstimatorType::anomalyDetector();
    }

    /**
     * Return the data types that this estimator is compatible with.
     *
     * @return \Rubix\ML\DataType[]
     */
    public function compatibility() : array
    {
        return [
            DataType::continuous(),
        ];
    }

    /**
     * Return the settings of the hyper-parameters in an associative array.
     *
     * @return mixed[]
     */
    public function params() : array
    {
        return $this->params;
    }

    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return isset($this->model);
    }

    /**
     * Train the learner with a dataset.
     *
     * @param \Rubix\ML\Datasets\Dataset $dataset
     * @throws \InvalidArgumentException
     */
    public function train(Dataset $dataset) : void
    {
        DatasetIsNotEmpty::check($dataset);
        SamplesAreCompatibleWithEstimator::check($dataset, $this);

        $this->model = $this->svm->train($dataset->samples());
    }

    /**
     * Make predictions from a dataset.
     *
     * @param \Rubix\ML\Datasets\Dataset $dataset
     * @throws \RuntimeException
     * @return int[]
     */
    public function predict(Dataset $dataset) : array
    {
        if (!$this->model) {
            throw new RuntimeException('Estimator has not been trained.');
        }

        $predictions = [];

        foreach ($dataset->samples() as $sample) {
            $predictions[] = $this->model->predict($sample) !== 1.0 ? 0 : 1;
        }

        return $predictions;
    }

    /**
     * Save the model data to the filesystem.
     *
     * @param string $path
     * @throws \RuntimeException
     */
    public function save(string $path) : void
    {
        if (!$this->model) {
            throw new RuntimeException('Learner must be'
                . ' trained before saving.');
        }

        $this->model->save($path);
    }

    /**
     * Load model data from the filesystem.
     *
     * @param string $path
     */
    public function load(string $path) : void
    {
        $this->model = new svmmodel($path);
    }
}
