<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.10.12
 * Time: 15:25
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;

class RiaCompanyInformationFourType extends AbstractType
{
    /** @var \Wealthbot\UserBundle\Entity\User $user */
    protected $user;

    /** @var bool $isPreSave */
    protected $isPreSave;

    public function __construct(User $user, $isPreSave = false)
    {
        $this->user = $user;
        $this->isPreSave = $isPreSave;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$riaCompanyInformation = $this->user->getRiaCompanyInformation();
        /*if ($riaCompanyInformation) {
            $isSearchable = is_null($riaCompanyInformation->getIsSearchableDb()) ? true : $riaCompanyInformation->getIsSearchableDb();
        } else {
            $isSearchable = true;
        }*/

        $builder
            ->add('is_searchable_db', 'hidden', array(
//                'choices' => array( true => 'Yes', false => 'No'),
//                'required' => false,
//                'expanded' => true,
                'data' => true,
//                'hidden' => true
            ))
            ->add('min_asset_size', 'number', array(
                'precision' => 2,
                'grouping' => true,
                'required' => false
            ))
            ->add('logo_file', 'file', array('required' => false))
        ;

        if (!$this->isPreSave) {
            $this->addOnBindValidator($builder);
        }
    }

    protected function addOnBindValidator(FormBuilderInterface $builder)
    {
        $builder->addEventListener(\Symfony\Component\Form\FormEvents::BIND, function(\Symfony\Component\Form\FormEvent $event){
            $form = $event->getForm();

            /** @var \Wealthbot\RiaBundle\Entity\RiaCompanyInformation $data */
            $data = $event->getData();

            /*$isSearchable = $form->get('is_searchable_db')->getData();
            if (!$isSearchable) {
                $data->setMinAssetSize(null);
            } else
            */
            if (!is_numeric($data->getMinAssetSize())) {
                $form->get('min_asset_size')->addError(new \Symfony\Component\Form\FormError('Required.'));
            }

            // If ria already have logo do not show error for him
            if (!$data->getLogo() && !is_object($form->get('logo_file')->getData())) {
                $form->get('logo_file')->addError(new FormError('Required.'));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaCompanyInformation'
        ));
    }

    public function getName()
    {
        return 'wealthbot_riabundle_riacompanyinformationtype';
    }
}
