<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('name', TextType::class, [
				'label' => 'Nom du produit',
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'Entrez le nom du produit',
					'autocomplete' => 'off'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('description', TextareaType::class, [
				'label' => 'Description',
				'required' => false,
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'Décrivez le produit en détail',
					'rows' => 4
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('price', NumberType::class, [
				'label' => 'Prix HT (€)',
				'scale' => 2,
				'attr' => [
					'class' => 'form-control',
					'placeholder' => '0.00',
					'step' => '0.01',
					'min' => '0'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('stock', IntegerType::class, [
				'label' => 'Stock disponible',
				'attr' => [
					'class' => 'form-control',
					'placeholder' => '0',
					'min' => 0
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('mainImageFile', FileType::class, [
				'label' => 'Image principale',
				'mapped' => false,
				'required' => false,
				'attr' => [
					'class' => 'form-control',
					'accept' => 'image/*',
					'onchange' => 'previewImage(this)'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('additionalImages', FileType::class, [
				'label' => 'Images supplémentaires',
				'mapped' => false,
				'required' => false,
				'multiple' => true,
				'attr' => [
					'class' => 'form-control',
					'accept' => 'image/*',
					'multiple' => true,
					'onchange' => 'previewMultipleImages(this)'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('mainImage', TextType::class, [
				'label' => 'URL de l\'image principale (optionnel)',
				'required' => false,
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'https://example.com/image.jpg'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('sku', TextType::class, [
				'label' => 'Référence (SKU)',
				'required' => false,
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'PROD-001',
					'autocomplete' => 'off'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('category', EntityType::class, [
				'class' => Category::class,
				'choice_label' => 'name',
				'label' => 'Catégorie',
				'attr' => [
					'class' => 'form-select'
				],
				'row_attr' => ['class' => 'mb-3']
			])
			->add('isActive', CheckboxType::class, [
				'label' => 'Produit actif',
				'required' => false,
				'attr' => [
					'class' => 'form-check-input'
				],
				'row_attr' => ['class' => 'mb-3 form-check']
			])
			->add('isFeatured', CheckboxType::class, [
				'label' => 'Mettre en avant',
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
			'data_class' => Product::class,
		]);
	}
}


