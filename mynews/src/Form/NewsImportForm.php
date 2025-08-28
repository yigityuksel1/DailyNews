<?php

namespace Drupal\mynews\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NewsImportForm extends FormBase {

  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function getFormId(): string {
    return 'mynews_import_form';
  }
  //tek butonlu basit bir form oluşturma metodu
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['desc'] = [
      '#markup' => $this->t('Fetch latest news from NewsAPI and save them into the News content type.'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Fetch News Now'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    try {     // bolca try catch kullanmaya çalıştım
      $wsdata_service = \Drupal::service('wsdata');
      if (!$wsdata_service) {
        throw new \Exception('WSData service not available.');
      }

      // etm üzerinden WSCall entitysini yükle
      $wscall = $this->entityTypeManager
        ->getStorage('wscall')
        ->load('top_headlines');

      if (!$wscall) {
        $this->messenger()->addError($this->t('WSCall "top_headlines" not found.'));
        return;
      }

      // WSData call
      $articles = $wsdata_service->call($wscall);

    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('WSData call failed: @msg', ['@msg' => $e->getMessage()]));
      return;
    }

    if (empty($articles)) {
      $this->messenger()->addStatus($this->t('No articles were fetched.'));
      return;
    }

    $created = 0;
    $updated = 0;
    $node_storage = $this->entityTypeManager->getStorage('node');

    foreach ($articles as $a) {
      if (empty($a['title']) || empty($a['url'])) {  //boşsa kod bozulmadan skip işlemi
        continue;
      }

      $ids = $node_storage->getQuery()               //url karşılaştırması yapan basit bir sorgu
        ->condition('type', 'news')
        ->condition('field_url.uri', $a['url'])  
        ->range(0, 1)
        ->accessCheck(FALSE)                         //eklemediğimde hata veriyor 
        ->execute();

      $node = $ids ? $node_storage->load(reset($ids)) : NULL;

      $values = [                                    //apiden gelen bilgileri drupal fieldlarına atar
        'type' => 'news',
        'title' => $a['title'],
        'status' => 1,
        'field_description' => ['value' => $a['description'] ?? '', 'format' => 'basic_html'],
        'field_content' => ['value' => $a['content'] ?? '', 'format' => 'basic_html'],
        'field_url' => ['uri' => $a['url'], 'title' => $a['title']],
        'field_image_url' => !empty($a['urlToImage']) ? ['uri' => $a['urlToImage'], 'title' => 'Image'] : [],
        'field_source_name' => $a['source']['name'] ?? '',
        'field_author' => $a['author'] ?? '',
        'field_published_at' => !empty($a['publishedAt']) ? ['value' => substr($a['publishedAt'], 0, 19)] : NULL,
      ];

      if ($node) {                                 //eğer haber db de varsa update yoksa create ve count++ işlemi
        foreach ($values as $k => $v) {
          if ($k === 'type' || $k === 'title') continue;
          $node->set($k, $v);
        }
        $node->setTitle($values['title']);
        $node->save();
        $updated++;
      } else {
        Node::create($values)->save();
        $created++;
      }
    }
       // kullanıcıya mesaj verir
    $this->messenger()->addStatus($this->t('Import finished. @c created, @u updated.', [
      '@c' => $created,
      '@u' => $updated,
    ]));
  }
}
