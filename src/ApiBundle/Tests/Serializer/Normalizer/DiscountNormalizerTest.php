<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\ApiBundle\Tests\Serializer\Normalizer;

use Money\Currency;
use PHPUnit\Framework\TestCase;
use SolidInvoice\ApiBundle\Serializer\Normalizer\DiscountNormalizer;
use SolidInvoice\CoreBundle\Entity\Discount;
use SolidInvoice\MoneyBundle\Entity\Money;
use SolidInvoice\MoneyBundle\Formatter\MoneyFormatter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DiscountNormalizerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Money::setBaseCurrency('USD');
    }

    public function testSupportsNormalization(): void
    {
        $parentNormalizer = new class() implements NormalizerInterface, DenormalizerInterface {
            public function normalize($object, $format = null, array $context = [])
            {
                return $object;
            }

            public function supportsNormalization($data, $format = null)
            {
                return true;
            }

            public function supportsDenormalization($data, $type, $format = null)
            {
                return true;
            }

            public function denormalize($data, $class, $format = null, array $context = [])
            {
                return $data;
            }
        };

        $currency = new Currency('USD');
        $normalizer = new DiscountNormalizer($parentNormalizer, new MoneyFormatter('en', $currency), $currency);

        self::assertTrue($normalizer->supportsNormalization(new Discount()));
        self::assertFalse($normalizer->supportsNormalization(Discount::class));
    }

    public function testSupportsDenormalization(): void
    {
        $parentNormalizer = new class() implements NormalizerInterface, DenormalizerInterface {
            public function normalize($object, $format = null, array $context = [])
            {
                return $object;
            }

            public function supportsNormalization($data, $format = null)
            {
                return true;
            }

            public function supportsDenormalization($data, $type, $format = null)
            {
                return true;
            }

            public function denormalize($data, $class, $format = null, array $context = [])
            {
                return $data;
            }
        };

        $currency = new Currency('USD');
        $normalizer = new DiscountNormalizer($parentNormalizer, new MoneyFormatter('en', $currency), $currency);

        self::assertTrue($normalizer->supportsDenormalization(null, Discount::class));
        self::assertFalse($normalizer->supportsDenormalization([], NormalizerInterface::class));
    }

    public function testNormalization(): void
    {
        $parentNormalizer = new class() implements NormalizerInterface, DenormalizerInterface {
            public function normalize($object, $format = null, array $context = [])
            {
                return $object;
            }

            public function supportsNormalization($data, $format = null)
            {
                return true;
            }

            public function supportsDenormalization($data, $type, $format = null)
            {
                return true;
            }

            public function denormalize($data, $class, $format = null, array $context = [])
            {
                return $data;
            }
        };

        $currency = new Currency('USD');
        $normalizer = new DiscountNormalizer($parentNormalizer, new MoneyFormatter('en', $currency), $currency);

        $discount = new Discount();
        $discount->setType(Discount::TYPE_MONEY);
        $discount->setValue(100);

        self::assertEquals(['type' => 'money', 'value' => new \Money\Money(10000, $currency)], $normalizer->normalize($discount));
    }

    public function testDenormalization(): void
    {
        $parentNormalizer = new class() implements NormalizerInterface, DenormalizerInterface {
            public function normalize($object, $format = null, array $context = [])
            {
                return $object;
            }

            public function supportsNormalization($data, $format = null)
            {
                return true;
            }

            public function supportsDenormalization($data, $type, $format = null)
            {
                return true;
            }

            public function denormalize($data, $class, $format = null, array $context = [])
            {
                return $data;
            }
        };

        $currency = new Currency('USD');
        $normalizer = new DiscountNormalizer($parentNormalizer, new MoneyFormatter('en', $currency), $currency);

        $discount = new Discount();
        $discount->setType(Discount::TYPE_MONEY);
        $discount->setValue(10000);

        self::assertEquals($discount, $normalizer->denormalize(['type' => 'money', 'value' => 10000], Discount::class));
    }
}
