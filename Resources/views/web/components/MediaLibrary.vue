<template>
	<a-modal
		v-model:visible="state.isVisible"
		:footer="null"
		:closable="false"
		destroy-on-close
		:mask-closable="false"
		:width="800"
		@cancel="onCloseModal"
	>
		<template #title>
			<div class="media-library-header">
				<span class="media-library-title">
					<NewbieIcon icon="DropboxOutlined"></NewbieIcon>
					<span style="margin-left: 10px">媒体库</span>
				</span>
				<div class="media-library-actions">
					<a @click="onCloseModal">
						<NewbieIcon icon="CloseOutlined" :style="{ fontSize: '20px', marginTop: '2px' }"></NewbieIcon>
					</a>
				</div>
			</div>
		</template>
		<NewbieUploader
			ref="uploader"
			v-model="state.uploadHolder"
			type="file"
			:accept="accepts[state.currentCategory] || ''"
			:action="config.uploadUrl"
			:extra-data="{ ...extraData, category: state.currentCategory }"
			:max-size="500"
			:upload-text="uploadText"
			@success="onUploadSuccess"
		></NewbieUploader>

		<a-tabs v-model:activeKey="state.currentCategory" tab-position="left" animated>
			<a-tab-pane v-for="cat in categories" :key="cat.value" :tab="cat.label">
				<div class="media-library-content-container">
					<div class="media-library-content-items">
						<a-list
							:data-source="medias[state.currentCategory].pagination.items"
							:grid="state.currentCategory === 'image' ? { gutter: 16, sm: 4 } : null"
							:loading="medias[state.currentCategory].pagination.loading"
						>
							<template #loadMore>
								<div
									v-if="!medias[state.currentCategory].pagination.loading && !medias[state.currentCategory].pagination.finished"
									:style="{ textAlign: 'center', marginTop: '12px', height: '32px', lineHeight: '32px' }"
								>
									<a-button @click="onLoadMore(false)">加载更多</a-button>
								</div>
							</template>

							<template #renderItem="{ item }">
								<a-list-item v-if="state.currentCategory === 'image'">
									<a-card>
										<a-image
											class="media-library-image"
											:src="item.url || item.media.url"
											:width="104"
											:height="104"
											fallback="data:image/png;base64,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"
											:alt="item.name"
										/>
										<template #actions>
											<a-tooltip title="选择">
												<a-button type="link" size="small" @click="onSelectMedia(item.media)">
													<template #icon>
														<NewbieIcon icon="CheckSquareOutlined" style="font-size: 12px"></NewbieIcon>
													</template>
												</a-button>
											</a-tooltip>

											<a-tooltip title="下载">
												<a-button type="link" size="small" @click="onDownload(item.media)">
													<template #icon>
														<NewbieIcon icon="CloudDownloadOutlined" style="font-size: 12px"></NewbieIcon>
													</template>
												</a-button>
											</a-tooltip>

											<a-popconfirm title="是否确定删除当前内容?" ok-text="是" cancel-text="否" @confirm="onDeleteMedia(item)">
												<a-tooltip title="删除">
													<a-button type="link" size="small">
														<template #icon>
															<NewbieIcon icon="DeleteOutlined"></NewbieIcon>
														</template>
													</a-button>
												</a-tooltip>
											</a-popconfirm>
										</template>
									</a-card>
								</a-list-item>
								<a-list-item v-else>
									<template #actions>
										<a-tooltip title="选择">
											<a key="list-edit" @click="onSelectMedia(item.media)">
												<NewbieIcon icon="CheckSquareOutlined"></NewbieIcon>
											</a>
										</a-tooltip>
										<a-tooltip title="下载">
											<a key="list-download" @click="onDownload(item.media)">
												<NewbieIcon icon="CloudDownloadOutlined"></NewbieIcon>
											</a>
										</a-tooltip>

										<a-popconfirm title="是否确定删除当前内容?" ok-text="是" cancel-text="否" @confirm="onDeleteMedia(item)">
											<a-tooltip title="删除">
												<a key="list-delete">
													<NewbieIcon icon="DeleteOutlined"></NewbieIcon>
												</a>
											</a-tooltip>
										</a-popconfirm>
									</template>
									<a-skeleton avatar :title="false" :loading="!!medias[state.currentCategory].pagination.loading" active>
										<a-list-item-meta :description="item.size ? calcSize(item.size) : ''">
											<template #title>
												{{ item.name }}
											</template>
											<template #avatar>
												<div style="font-size: 32px">
													<NewbieIcon icon="FileExcelOutlined" v-if="['xls', 'xlsx'].includes(item.extension)"></NewbieIcon>
													<NewbieIcon
														icon="FileWordOutlined"
														v-else-if="['doc', 'docx'].includes(item.extension)"
													></NewbieIcon>
													<NewbieIcon
														icon="FilePptOutlined"
														v-else-if="['ppt', 'pptx'].includes(item.extension)"
													></NewbieIcon>
													<NewbieIcon icon="FilePdfOutlined" v-else-if="['pdf'].includes(item.extension)"></NewbieIcon>
													<NewbieIcon
														icon="FileZipOutlined"
														v-else-if="['zip', 'rar', '7z'].includes(item.extension)"
													></NewbieIcon>
													<NewbieIcon icon="FileUnknownOutlined" v-else></NewbieIcon>
												</div>
											</template>
										</a-list-item-meta>
										<div>{{ item.created_at_human }}</div>
									</a-skeleton>
								</a-list-item>
							</template>
						</a-list>
					</div>
				</div>
			</a-tab-pane>
		</a-tabs>
	</a-modal>
</template>

<script setup>
import { computed, nextTick, reactive, ref, watch } from "vue"

import { NewbieIcon, NewbieUploader } from "@web/components"
import { useMediaStore } from "@manager/stores"
import { useFetch } from "@/js/hooks/common/network"
import { useProcessStatusSuccess } from "@/js/hooks/web/form"
import { message } from "ant-design-vue"
import { find } from "lodash-es"

const mediaStore = useMediaStore()

const props = defineProps({
	visible: {
		type: Boolean,
		required: false,
	},
	trigger: {
		type: String,
		default: "click",
		validator(value) {
			return ["click", "hover"].includes(value)
		},
	},
	category: {
		type: String,
		default: "image",
	},
	extraData: {
		// 发送请求时的额外参数
		type: Object,
		default: () => ({}),
	},
})

const emits = defineEmits(["update:visible", "select"])

const accepts = {
	image: ".jpg,.jpeg,.png,.gif",
	video: ".mp4,.avi,.mov",
	audio: ".wav,.mp3,.aac",
	document: ".doc,docx,.xls,.xlsx,.ppt,.pptx,.pdf",
}

const uploader = ref(null)

const config = computed(() => mediaStore.config)
const categories = computed(() => mediaStore.categories)
const medias = computed(() => mediaStore.medias)

const state = reactive({
	isVisible: props.visible,
	currentCategory: props.category,
	uploadHolder: {},
})

const uploadText = computed(() => {
	return `上传 ${find(categories.value, { value: state.currentCategory }).label}`
})
const onLoadMore = (refresh) => {
	mediaStore.loadMore(state.currentCategory, { ...props.extraData, page_size: 20 }, refresh)
}

watch(
	() => props.visible,
	(visible) => {
		state.isVisible = visible
		if (visible && !medias.value[state.currentCategory]?.pagination.items?.length) {
			nextTick(() => {
				onLoadMore(true)
			})
		}
	},
)

watch(
	() => state.currentCategory,
	(category) => {
		if (!medias.value[category]?.pagination.items?.length) {
			onLoadMore(true)
		}
	},
)

const onCloseModal = () => {
	emits("update:visible", false)
}

const onUploadSuccess = async (file) => {
	if (file.id) {
		await nextTick(() => {
			uploader.value.clearFileList()
		})
		const res = await useFetch().get(`${config.value.itemUrl}/${file.id}`)
		mediaStore.addMedia(res.result, state.currentCategory)
	}
}

const onSelectMedia = (media) => {
	emits("select", media)
	state.isVisible = false
	onCloseModal()
}

const onDownload = (media) => {
	window.open(media.url)
}

const onDeleteMedia = async (item) => {
	const res = await useFetch().post(config.value.deleteUrl, { id: item.id })
	useProcessStatusSuccess(res, () => {
		mediaStore.deleteMedia(item, state.currentCategory)
		message.success("删除成功")
	})
}

const calcSize = (size) => {
	const kb = (size / 1024).toFixed(2)

	if (kb > 1024) {
		return `${(kb / 1024).toFixed(2)}MB`
	}
	return `${kb}KB`
}
</script>

<style lang="less" scoped>
.media-library-header {
	display: flex;
	justify-content: space-between;
	align-items: center;

	.media-library-title {
		flex-grow: 1;
	}

	.media-library-actions {
		display: flex;
		align-items: center;

		a {
			margin-left: 10px;
			cursor: pointer;
		}
	}
}

.media-library-content-container {
	width: 640px;

	.media-library-content-items {
		display: flex;
		height: 400px;
		overflow-y: auto;

		:deep(.ant-list) {
			width: 100%;

			.ant-spin-nested-loading {
				.ant-spin-container {
					.ant-row {
						margin: 0 !important;
					}
				}
			}
		}

		:deep(.ant-card) {
			.ant-card-body {
				padding: 0;
				display: flex;
				align-items: center;
				justify-content: center;
				box-sizing: border-box;
			}

			.ant-card-actions {
				display: none;
				position: relative;
				margin-top: -26px;
				z-index: 1000;

				li {
					margin: 0;
				}
			}

			&:hover {
				.ant-card-actions {
					transition: all ease 0.3s;
					display: flex;
				}
			}
		}

		:deep(.ant-image) {
			display: flex;
			align-items: center;
			justify-content: center;
			box-sizing: border-box;
		}
	}
}

:deep(.newbie-uploader) {
	width: 104px;
	flex-shrink: 0;
	flex-grow: 0;
	margin-right: 10px;

	.ant-upload-select-picture-card,
	.ant-upload-list-picture-card-container {
		margin: 0;
	}
}
</style>
