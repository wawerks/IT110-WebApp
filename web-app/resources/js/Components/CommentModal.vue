<template>
  <div v-show="show" class="fixed inset-0 z-[1000] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background overlay -->
    <div class="fixed inset-0">
      <div class="absolute inset-0 bg-gray-500/75 backdrop-blur-md"></div>
    </div>

    <!-- Modal panel -->
    <div class="fixed inset-0 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
          <!-- Modal content -->
          <div class="bg-white">
            <div class="h-full max-h-[80vh] overflow-y-auto px-4 pb-4 pt-5 sm:p-6">
              <div class="w-full relative">
                <button 
                  @click="$emit('close')" 
                  class="absolute right-0 top-0 text-gray-400 hover:text-gray-500 focus:outline-none"
                >
                  <span class="sr-only">Close</span>
                  <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                  Item Details
                </h3>

                <!-- Item Details -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                  <div class="space-y-3">
                    <!-- Status Label -->
                    <div :class="item.isFound ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                      class="uppercase text-xs font-semibold py-1 px-3 rounded-full inline-block">
                      {{ item.isFound ? 'FOUND' : 'LOST' }}
                    </div>
                    
                    <!-- User Name and Item Name -->
                    <div class="space-y-2">
                      <h4 class="text-xl font-semibold text-gray-800">{{ item.userName }}</h4>
                      <div class="flex items-center space-x-2">
                        <h3 class="text-gray-600 font-medium">Item name:</h3>
                        <p class="text-gray-700">{{ item.item_name }}</p>
                      </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="flex items-center space-x-2">
                      <h3 class="text-gray-600 font-medium">Item Description:</h3>
                      <p class="text-gray-700">{{ item.description }}</p>
                    </div>

                    <!-- Image -->
                    <div v-if="item.image_url" class="mt-4">
                      <img :src="item.image_url" alt="Item Image" class="w-full h-auto rounded-lg object-cover max-h-[300px]" />
                    </div>
                    
                    <!-- Posted Date -->
                    <p class="text-sm text-gray-500">Posted on: {{ formatDate(item.created_at) }}</p>
                  </div>
                </div>

                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                  Comments
                </h3>

                <!-- Comments List -->
                <div class="space-y-4">
                  <div v-for="comment in sortedComments" :key="comment.id" class="bg-gray-50 p-4 rounded-lg">
                    <div class="space-y-1">
                      <div class="font-medium text-gray-900">
                        {{ comment.user?.name || comment.user_name || 'Anonymous' }} commented:
                      </div>
                      <p class="text-gray-700">{{ comment.text }}</p>
                      <div class="text-xs text-gray-500">
                        {{ formatDate(comment.created_at) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Comment Input -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6">
              <div class="flex items-center space-x-4">
                <input 
                  v-model="newComment" 
                  type="text" 
                  placeholder="Add a comment..."
                  class="flex-1 p-3 border rounded-lg focus:ring-2 focus:ring-teal-500"
                  @keyup.enter="submitComment"
                />
                <button 
                  @click="submitComment"
                  :disabled="!newComment?.trim()"
                  class="bg-teal-500 text-white px-4 py-2 rounded-lg disabled:opacity-50 hover:bg-teal-600 transition-colors"
                >
                  Post
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'CommentModal',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    comments: {
      type: Array,
      required: true,
      default: () => []
    },
    item: {
      type: Object,
      required: true,
      default: () => ({
        isFound: false,
        userName: '',
        item_name: '',
        description: '',
        image_url: '',
        created_at: new Date().toISOString()
      })
    },
    itemId: {
      type: [Number, String],
      required: true
    },
    itemType: {
      type: String,
      required: true
    }
  },
  emits: ['close', 'submit-comment'],
  setup(props, { emit }) {
    const newComment = ref('');

    // Sort comments by date, newest first
    const sortedComments = computed(() => {
      return [...props.comments].sort((a, b) => {
        return new Date(b.created_at) - new Date(a.created_at);
      });
    });

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const submitComment = () => {
      if (newComment.value.trim()) {
        emit('submit-comment', {
          itemId: props.itemId,
          text: newComment.value,
          itemType: props.itemType
        });
        newComment.value = '';
      }
    };

    return {
      newComment,
      submitComment,
      formatDate,
      sortedComments
    };
  }
};
</script>

<style scoped>
.backdrop-blur-md {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Hide scrollbar for Chrome, Safari and Opera */
::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
* {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}
</style>