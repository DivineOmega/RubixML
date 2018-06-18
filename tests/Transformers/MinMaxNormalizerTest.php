<?php

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Transformers\Transformer;
use Rubix\ML\Transformers\MinMaxNormalizer;
use PHPUnit\Framework\TestCase;

class MinMaxMinMaxNormalizerTest extends TestCase
{
    protected $dataset;

    protected $transformer;

    public function setUp()
    {
        $this->dataset = new Unlabeled([
            [1, 2, 3, 4],
            [40, 20, 30, 10],
            [100, 300, 200, 400],
        ]);

        $this->transformer = new MinMaxNormalizer();
    }

    public function test_build_z_scale_standardizer()
    {
        $this->assertInstanceOf(MinMaxNormalizer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }

    public function test_transform_dataset()
    {
        $this->transformer->fit($this->dataset);

        $this->dataset->transform($this->transformer);

        $this->assertEquals([
            [0.0, 0.0, 0.0, 0.0],
            [0.3939393938996021, 0.06040268456173145, 0.13705583755649461, 0.015151515151132538],
            [0.9999999998989899, 0.999999999966443, 0.9999999999492385, 0.9999999999747474],
        ], $this->dataset->samples());
    }
}