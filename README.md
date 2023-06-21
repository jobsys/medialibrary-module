# **MediaLibrary** 媒体库模块

为了让媒体资源，如图片、视频等内容可以重复利用，该模块提供一个媒体库组件 `MediaLibrary`，并集成了媒体文件上传、展示、选择等操作。默认提供 `图片`、`视频`、`文件`、`音频`和`其它`五个分类，如果需要添加新分类可以在
系统字典 `媒体库资源类型` 中添加字典项。

## 模块安装

```bash
composer require jobsys/medialibrary-module
```

### 依赖

+ PHP 依赖 （无）

+ JS 依赖 （无）

### 配置

#### 模块配置

```php
"MediaLibrary" => [
    "route_prefix" => "manager",                                                    // 路由前缀
]
```

## 模块功能

### 媒体库功能

媒体库中的资源遵循 [权限模板](module-permission.md?id=数据权限) 中的数据权限定义。上传的文件将如果是公共读写则存放在 `storage/app/public`，私有读写的文件存在放
`storage/app/private`，并会以文件的 `MIME 类型/日期/`进行分类存储。如:

```
- public
  - application
    - vnd.openxmlformats-officedocument.wordprocessingml.document
      - 20230428
        - Test_9fc219c4a2683ad5d96db5d2cf38e7b9.docx
  - image
    - jpeg
      - 20230325
        - bg1_286000046dc1b3e0f5cc0be11b043c72.jpeg
      - 20230326
        - bg1_286000046dc1b3e0f5cc0be11b043c73.jpeg
```


#### 开发规范

1. 在页面引入 `MediaLibrary` 组件然后使用。
    ```js
    import MediaLibrary from "@modules/MediaLibrary/Resources/views/web/components/MediaLibrary.vue"
    ```
    ```html
    <MediaLibrary
			v-model:visible="state.showMediaLibrary"
			category="courseware"
			:extra-data="{ department_id: course.department_id }"
			@select="onSelectMedia"
	/>
    ```
   > 由于默认会对资源进行权限管理，所以需要在 `extra-data` 中传入当前操作的业务 `department_id`

   > 该上传支持`私有读写`，如果需要对资源`私有读写`，可以在 `extra-data` 中添加 `{type: private}`，如无设置则为公共读写。

## 模块代码

### 数据表

```bash
2014_10_12_000005_create_media_libraries_table.php             # 审核模块数据表
```

### 数据模型/Scope

```bash
Modules\MediaLibrary\Entitie\LibraryMedia             # 媒体文件
```

### Controller

```bash
Modules\MediaLibrary\Http\Controllers\MediaLibraryController        # 提供上传以及 CRUD 功能
```

### UI

#### PC 组件

```bash
web/components/MediaLibrary.vue        # 媒体库组件
```
