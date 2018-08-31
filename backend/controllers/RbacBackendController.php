<?php

namespace backend\controllers;

use common\auth\filters\Yii2Auth;
use common\auth\helpers\AuthHelper;
use common\components\ActionLogTracking;
use common\models\AuthItem;
use common\models\AuthItemChild;
use common\models\UserActivity;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class RbacBackendController extends BaseBEController
{
    public function behaviors()
    {
        return [
            'auth' => [
                'class'     => Yii2Auth::className(),
                'autoAllow' => false,
//                'authManager' => 'authManager',
                //                'except' => ['index'],
            ],
            [
                'class'              => ActionLogTracking::className(),
                'user'               => Yii::$app->user,
                'model_type_default' => UserActivity::ACTION_TARGET_TYPE_RBAC_BE,
                'post_action'        => [
                    ['action' => 'create-permission', 'accept_ajax' => true],
                    ['action' => 'create-role', 'accept_ajax' => false],
                    ['action' => 'update-permission', 'accept_ajax' => false],
                    ['action' => 'update-role', 'accept_ajax' => false],
                    ['action' => 'delete-permission', 'accept_ajax' => true],
                    ['action' => 'delete-role', 'accept_ajax' => true],
                    ['action' => 'generate-permission-confirm', 'accept_ajax' => true],
                    ['action' => 'generate-role-confirm', 'accept_ajax' => true],
                    ['action' => 'role-revoke-auth-item', 'accept_ajax' => true],
                    ['action' => 'role-add-auth-item', 'accept_ajax' => true],
                ],
                // 'only'               => [
                //     'create-permission',
                //     'create-role',
                //     'update-permission',
                //     'update-role',
                //     'delete-permission',
                //     'delete-role',
                //     'generate-role',
                //     'generate-permission-confirm',
                //     'generate-role-confirm',
                //     'role-revoke-auth-item',
                //     'role-add-auth-item'],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionPermission()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => AuthItem::findPermission()->andWhere(['acc_type' => AuthItem::ACC_TYPE_BACKEND]),
        ]);

//        $searchModel = new UserSearch();
        //        $searchModel->type=User::TYPE_ADMIN;
        //        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('permission', [
            'dataProvider' => $dataProvider,
//            'searchModel' => $searchModel
        ]);
    }

    public function actionRole()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => AuthItem::findRole()->andWhere(['acc_type' => AuthItem::ACC_TYPE_BACKEND]),
        ]);

//        $searchModel = new UserSearch();
        //        $searchModel->type=User::TYPE_ADMIN;
        //        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('role', [
            'dataProvider' => $dataProvider,
//            'searchModel' => $searchModel
        ]);
    }

    /**
     * Displays a single User model.
     * @param  $id
     * @return mixed
     */
    public function actionViewPermission($name)
    {
        return $this->render('view-permission', [
            'model' => $this->findPermission($name),
        ]);
    }

    /**
     * Displays a single User model.
     * @param  $id
     * @return mixed
     */
    public function actionViewRole($name)
    {
        return $this->render('view-role', [
            'model' => $this->findRole($name),
        ]);
    }

    /**
     * @param  $name
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findPermission($name)
    {
        if (($model = AuthItem::findOne(['name' => $name, 'type' => AuthItem::TYPE_PERMISSION])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param  $name
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findRole($name)
    {
        if (($model = AuthItem::findOne(['name' => $name, 'type' => AuthItem::TYPE_ROLE])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return string|Response
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function actionCreatePermission()
    {
        $model = new AuthItem();

        if ($model->load(Yii::$app->request->post())) {
            $model->type       = AuthItem::TYPE_PERMISSION;
            $model->created_at = time();
            if (!$model->save()) {
                Yii::error($model->getErrors());
            } else {
                \Yii::$app->getSession()->setFlash('success', \Yii::t('app', 'Tạo 01 quyền thành công'));
                return $this->redirect(['view-permission', 'name' => $model->name]);
            }
        }

        return $this->render('create-permission', [
            'model' => $model,
        ]);
    }

    /**
     * @return string|Response
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function actionCreateRole()
    {
        $model = new AuthItem();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->type     = AuthItem::TYPE_ROLE;
            $model->acc_type = AuthItem::ACC_TYPE_BACKEND;

            if (!$model->save()) {
                Yii::error($model->getErrors());
            } else {
                \Yii::$app->getSession()->setFlash('success', \Yii::t('app', 'Thêm mới thành công'));
                return $this->redirect(['view-role', 'name' => $model->name]);
            }
        }

        return $this->render('create-role', [
            'model' => $model,
        ]);
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function actionUpdatePermission($name)
    {
        $model = $this->findPermission($name);

        if ($model->load(Yii::$app->request->post())) {
            $model->updated_at = time();
            if (!$model->save()) {
                Yii::error($model->getErrors());
            } else {
                \Yii::$app->getSession()->setFlash('success', \Yii::t('app', 'Cập nhật 01 quyền thành công'));
                return $this->redirect(['view-permission', 'name' => $model->name]);
            }
        }

        return $this->render('update-permission', [
            'model' => $model,
        ]);
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function actionUpdateRole($name)
    {
        $model = $this->findRole($name);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->acc_type = AuthItem::ACC_TYPE_BACKEND;
            if (!$model->save()) {
                Yii::error($model->getErrors());
            } else {
                \Yii::$app->getSession()->setFlash('success', \Yii::t('app', 'Cập nhật thành công'));
                return $this->redirect(['view-role', 'name' => $model->name]);
            }
        }

        return $this->render('update-role', [
            'model' => $model,
        ]);
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function actionDeletePermission($name)
    {
        $this->findPermission($name)->delete();

        return $this->redirect(['permission']);
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function actionDeleteRole($name)
    {
        $this->findRole($name)->delete();

        \Yii::$app->getSession()->setFlash('success', \Yii::t('app', 'Xóa thành công'));
        return $this->redirect(['role']);
    }

    /**
     * @return mixed
     */
    public function actionGeneratePermission()
    {
        $dataProvider = new ArrayDataProvider([
            'allModels'  => AuthHelper::listActions('@backend', true),
            'key'        => 'name',
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('generate-permission', [
            'dataProvider' => $dataProvider,
//            'searchModel' => $searchModel
        ]);
//
    }

    /**
     * @return mixed
     */
    public function actionGenerateRole()
    {
        $dataProvider = new ArrayDataProvider([
            'allModels'  => AuthHelper::listControllers('@backend', true),
            'key'        => 'name',
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('generate-role', [
            'dataProvider' => $dataProvider,
//            'searchModel' => $searchModel
        ]);
//
    }

    public function actionGeneratePermissionConfirm()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post                       = Yii::$app->request->post();
        if (isset($post['ids'])) {
            $ids     = $post['ids'];
            $actions = AuthHelper::listActions('@backend', true);

            $count = 0;
            foreach ($actions as $action) {
                if ($action->isNameIn($ids) && $action->createPermission()) {
                    $count++;
                }
            }
            return [
                'success' => true,
                'message' => \Yii::t('app', "Tạo {count} quyền thành công!", ['count' => $count]),
            ];
        } else {
            return [
                'success' => false,
                'message' => \Yii::t('app', 'Bad request'),
            ];
        }
    }

    public function actionGenerateRoleConfirm()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post                       = Yii::$app->request->post();
        if (isset($post['ids'])) {
            $ids         = $post['ids'];
            $controllers = AuthHelper::listControllers('@backend', true); //FIXME: cuongvm 20150326 -  fixbug không tự add full. false->true

            $count = 0;
            foreach ($controllers as $controller) {
                if ($controller->isNameIn($ids) && $controller->createRoleIfNotExist()) {
                    $controller->autoPermissionAssign();
                    $count++;
                }
            }
            return [
                'success' => true,
                'message' => \Yii::t('app', "Tạo {count} chức danh thành công!", ['count' => $count]),
            ];
        } else {
            return [
                'success' => false,
                'message' => \Yii::t('app', 'Bad request'),
            ];
        }
    }

    /**
     * Revoke item from role
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param  $id
     * @return mixed
     */
    public function actionRoleRevokeAuthItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post                       = Yii::$app->request->post();

        $success = false;
        $message = "Tham số không đúng";

        if (isset($post['parent']) && isset($post['child'])) {
            $parent = $post['parent'];
            $child  = $post['child'];

            $mapping = AuthItemChild::find()->andWhere(['child' => $child, 'parent' => $parent])->one();
            if ($mapping) {
                if ($mapping->delete()) {
                    $success = true;
                    $message = \Yii::t('app', "Đã xóa quyền {child} khỏi nhóm quyền {parent}!", ['child' => $child, 'parent' => $parent]);
                } else {
                    $message = \Yii::t('app', "Lỗi hệ thống, vui lòng thử lại sau");
                }
            } else {
                $message = \Yii::t('app', "Quyền {child} không có trong nhóm quyền {parent}!", ['child' => $child, 'parent' => $parent]);
            }

        }

        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    /**
     * add items to role
     * @param  $name
     * @return mixed
     */
    public function actionRoleAddAuthItem($name)
    {
        $model = AuthItem::findOne(['name' => $name, 'type' => AuthItem::TYPE_ROLE, 'acc_type' => AuthItem::ACC_TYPE_BACKEND]);

        Yii::$app->response->format = Response::FORMAT_JSON;

        $success = false;
        $message = \Yii::t('app', "Quyền/nhóm quyền không tồn tại'");

        if ($model) {
            $post = Yii::$app->request->post();

            if (isset($post['addItems'])) {
                $items = $post['addItems'];

                $count = 0;

                foreach ($items as $item) {
                    $child           = AuthItem::findOne(['name' => $item]);
                    $mapping         = new AuthItemChild();
                    $mapping->child  = $item;
                    $mapping->parent = $name;
                    if ($mapping->save()) {
                        $count++;
                    }
                }

                if ($count > 0) {
                    $success = true;
                    $message = \Yii::t('app', "Đã thêm {count} quyền/nhóm quyền vào nhóm '{name}'", ['count' => $count, 'name' => $name]);

                }
            }
        }

        return [
            'success' => $success,
            'message' => $message,
        ];
    }
}
