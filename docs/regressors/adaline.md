<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/Regressors/Adaline.php">[source]</a></span>

# Adaline
*Adaptive Linear Neuron* is a single layer neural network with a continuous linear output neuron. Training is equivalent to solving L2 regularized linear regression ([Ridge](ridge.md)) iteratively using Mini Batch Gradient Descent.

**Interfaces:** [Estimator](../estimator.md), [Learner](../learner.md), [Online](../online.md), [Verbose](../verbose.md), [Persistable](../persistable.md)

**Data Type Compatibility:** Continuous

## Parameters
| # | Param | Default | Type | Description |
|---|---|---|---|---|
| 1 | batch size | 100 | int | The number of training samples to process at a time. |
| 2 | optimizer | Adam | Optimizer | The gradient descent optimizer used to update the network parameters. |
| 3 | alpha | 1e-4 | float | The strength of the L2 regularization penalty. |
| 4 | epochs | 1000 | int | The maximum number of training epochs. i.e. the number of times to iterate over the entire training set before terminating. |
| 5 | min change | 1e-4 | float | The minimum change in the training loss necessary to continue training. |
| 6 | window | 5 | int | The number of epochs without improvement in the training loss to wait before considering an early stop. |
| 7 | cost fn | LeastSquares | RegressionLoss | The function that computes the loss associated with an erroneous activation during training. |

## Additional Methods
Return the training loss at each epoch:
```php
public steps() : array
```

Return the underlying neural network instance or `null` if untrained:
```php
public network() : Network|null
```

## Example
```php
use Rubix\ML\Regressors\Adaline;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\NeuralNet\CostFunctions\HuberLoss;

$estimator = new Adaline(10, new Adam(0.001), 500, 1e-6, 5, new HuberLoss(2.5));
```

### References
>- B. Widrow. (1960). An Adaptive "Adaline" Neuron Using Chemical "Memistors".