<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('name', TextType::class, [
				'label' => 'Nom',
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'Entrez le nom de la catégorie'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('description', TextType::class, [
				'label' => 'Description',
				'required' => false,
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'Décrivez la catégorie'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('imageFile', FileType::class, [
				'label' => 'Image de la catégorie',
				'mapped' => false,
				'required' => false,
				'attr' => [
					'class' => 'form-control',
					'accept' => 'image/*',
					'onchange' => 'previewImage(this)'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('image', TextType::class, [
				'label' => 'URL de l\'image (optionnel)',
				'required' => false,
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'https://example.com/image.jpg'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('isActive', CheckboxType::class, [
				'label' => 'Actif',
				'required' => false,
				'attr' => [
					'class' => 'form-check-input'
				],
				'row_attr' => ['class' => 'mb-3 form-check']
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Category::class,
		]);
	}
}


