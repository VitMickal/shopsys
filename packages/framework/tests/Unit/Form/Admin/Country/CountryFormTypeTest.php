<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\Admin\Country;

use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Admin\Country\CountryFormType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class CountryFormTypeTest extends TypeTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade
     */
    private $countryFacade;

    public function testNameIsMandatory(): void
    {
        $countryData = [
            'save' => '',
            'name' => [
                'en' => 'Czech republic',
                'cs' => '',
            ],
            'code' => 'CZ',
            'enabled' => [
                1 => '1',
                2 => '1',
            ],
            'priority' => [
                1 => '0',
                2 => '0',
            ],
        ];

        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryData);
        $this->assertFalse($countryForm->isValid());

        $countryData['name']['cs'] = 'Česká republika';

        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryData);
        $this->assertTrue($countryForm->isValid());
    }

    public function testPriorityIsNumber(): void
    {
        $countryData = [
            'save' => '',
            'name' => [
                'en' => 'Czech republic',
                'cs' => 'Česká republika',
            ],
            'code' => 'CZ',
            'enabled' => [
                1 => '1',
                2 => '1',
            ],
            'priority' => [
                1 => 'asd',
                2 => 0,
            ],
        ];

        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryData);
        $this->assertFalse($countryForm->isValid(), 'Invalid form');

        $countryData['priority'][1] = '1';

        $countryForm = $this->createCountryForm();
        $countryForm->submit($countryData);
        $this->assertTrue($countryForm->isValid(), 'Valid form');
    }

    protected function setUp()
    {
        $translator = $this->createMock(Translator::class);
        $translator->method('staticTrans')->willReturnArgument(0);
        $translator->method('staticTransChoice')->willReturnArgument(0);
        Translator::injectSelf($translator);

        $this->localization = $this->createMock(Localization::class);
        $this->localization->method('getLocalesOfAllDomains')->willReturn(['cs', 'en']);
        $this->localization->method('getAdminLocale')->willReturn('en');

        $this->domain = $this->createMock(Domain::class);
        $this->domain->method('getAll')
            ->willReturn([
                    new DomainConfig(1, '', '', 'cs'),
                    new DomainConfig(2, '', '', 'en'),
                ]);
        $this->domain->method('getAllIds')->willReturn([1, 2]);

        $this->countryFacade = $this->createMock(CountryFacade::class);
        $this->countryFacade->method('getByCode')->willReturn(null);

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension(
                [
                new CountryFormType($this->countryFacade),
                new LocalizedType($this->localization),
                new DomainsType($this->domain),
                new MultidomainType($this->domain),
                ],
                []
            ),
        ];
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createCountryForm(): FormInterface
    {
        return $this->factory->create(CountryFormType::class, null, ['country' => null]);
    }
}
