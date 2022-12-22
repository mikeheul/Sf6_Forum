<?php

namespace App\Form;

use App\Entity\Topic;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TopicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // le formulaire de création de topic inclut la création du 1er post du topic
        $builder
            ->add('title', TextType::class, [
                "attr" => ["class" => "form-control"]
            ]);
            // le champ first_message est en mapped false pour éviter une erreur pour l'objet Topic (qui ne possède pas d'attribut "first_message")
            if(!$options['edit']){
                $builder->add('first_message', TextareaType::class, [
                    "mapped" => false,
                    "attr" => [
                        "class" => "form-control",
                        "rows" => 8    
                    ]
                ]);
            }
            if(!$options['edit']){
                $builder->add('add', SubmitType::class, [
                    "attr" => ["class" =>  "btn btn-success mt-3 mb-3"]
                ]);
            } else {
                $builder->add('edit', SubmitType::class, [
                    "attr" => ["class" =>  "btn btn-success mt-3 mb-3"]
                ]);
            }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Topic::class,
            'edit' => false
        ]);
    }
}
