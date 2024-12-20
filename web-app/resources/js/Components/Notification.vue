<template>
  <div class="relative">
    <button class="relative focus:outline-none" @click="toggleDropdown">
      <svg class="w-6 h-6 text-gray-600 hover:text-teal-500" fill="none" stroke="currentColor" stroke-width="2"
        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h11z">
        </path>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22c1.656 0 3-1.343 3-3H9c0 1.657 1.344 3 3 3z">
        </path>
      </svg>

      <span v-if="unreadCount > 0"
        class="absolute top-0 right-0 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
        {{ unreadCount }}
      </span>
    </button>

    <div v-if="isOpen" class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
      <div class="max-h-[80vh] overflow-y-auto">
        <div class="py-1">
          <h3 class="px-4 py-2 text-lg font-medium text-gray-900">Notifications</h3>
            
          <!-- No notifications message -->
          <div v-if="notifications.length === 0" class="px-4 py-3 text-sm text-gray-700">
            No notifications
          </div>

          <!-- Notification items -->
          <div v-for="notification in notifications" 
            :key="notification.id"
            @click="openNotificationDetails(notification)"
            class="px-4 py-3 hover:bg-gray-100 cursor-pointer"
            :class="{ 'bg-gray-50': !notification.read_at }">
              
            <!-- User Info and Status -->
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-bold">
                  {{ notification.userName?.[0]?.toUpperCase() || 'U' }}
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ notification.data.commenter_name || notification.data.title }}</p>
                  <p class="text-sm text-gray-500">
                    <template v-if="notification.type === 'comment'">
                      commented on your {{ notification.data.item_type }} item 
                      <span class="font-medium">"{{ notification.data.item_name }}"</span>
                    </template>
                    <template v-else>
                      {{ notification.data.message }}
                    </template>
                  </p>
                </div>
              </div>
              <span v-if="!notification.read_at" 
                class="bg-red-500 w-2.5 h-2.5 rounded-full"
                title="Unread notification"></span>
            </div>
              
            <!-- Timestamp -->
            <p class="mt-1 text-sm text-gray-500">
              {{ formatDate(notification.created_at) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <CommentModal
      v-if="selectedItem"
      :show="showCommentModal"
      :item="selectedItem"
      :comments="selectedItem?.comments || []"
      :itemId="selectedItem.id"
      :itemType="selectedItem.isFound ? 'found' : 'lost'"
      @close="closeModal"
      @submit-comment="handleCommentSubmit"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import CommentModal from './CommentModal.vue';

const isOpen = ref(false);
const unreadCount = ref(0);
const notifications = ref([]);
const users = ref({});
const items = ref({});
const currentUserId = ref(null);
const selectedNotification = ref(null);
const showCommentModal = ref(false);
const selectedItem = ref(null);
const newComment = ref('');

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const openNotificationDetails = async (notification) => {
  try {
    // Parse the notification data if it's a string
    const notificationData = typeof notification.data === 'string' 
      ? JSON.parse(notification.data) 
      : notification.data;

    console.log('Opening notification:', {
      notification_id: notification.id,
      item_id: notificationData.item_id,
      item_type: notificationData.item_type,
      notification_data: notificationData
    });
    
    if (!notificationData || !notificationData.item_id || !notificationData.item_type) {
      console.error('Invalid notification data:', notification);
      return;
    }
    
    // Fetch item details using the correct API endpoints
    const itemUrl = `/${notificationData.item_type}-items/${notificationData.item_id}`;
    console.log('Fetching item details from:', itemUrl);
    const response = await axios.get(itemUrl);
    console.log('Fetched item details:', response.data);
    
    // Fetch comments for this item
    try {
      const commentsUrl = `/comments/${notificationData.item_type}/${notificationData.item_id}`;
      console.log('Fetching comments from:', commentsUrl);
      const commentsResponse = await axios.get(commentsUrl);
      console.log('Fetched comments:', commentsResponse.data);
      
      // Format comments with user names
      const comments = (commentsResponse.data.comments || commentsResponse.data || []).map(comment => ({
        ...comment,
        userName: comment.user?.name || comment.userName || 'Unknown User',
        created_at: formatDate(comment.created_at)
      }));
      
      // Set the item data with comments
      selectedItem.value = {
        ...response.data,
        id: notificationData.item_id,
        isFound: notificationData.item_type === 'found',
        userName: response.data.user?.name || response.data.userName || 'Unknown User', // Use the item owner's name from the user relationship
        item_name: response.data.name || response.data.item_name || notificationData.item_name || 'Unnamed Item',
        description: response.data.description || '',
        comments: comments
      };

      console.log('Selected item data:', selectedItem.value);
    } catch (commentsError) {
      console.error('Error fetching comments:', commentsError);
      selectedItem.value = {
        ...response.data,
        id: notificationData.item_id,
        isFound: notificationData.item_type === 'found',
        userName: response.data.user?.name || response.data.userName || 'Unknown User', // Use the item owner's name from the user relationship
        item_name: response.data.name || response.data.item_name || notificationData.item_name || 'Unnamed Item',
        description: response.data.description || '',
        comments: []
      };
    }
    
    // Show the modal
    showCommentModal.value = true;
    isOpen.value = false;
    await markAsRead(notification.id);
  } catch (error) {
    console.error('Error in openNotificationDetails:', error);
    console.error('Error details:', error.response?.data || error.message);
  }
};

const submitComment = async () => {
  if (!newComment.value.trim()) return;
  
  try {
    const commentData = {
      item_id: selectedItem.value.id,
      item_type: selectedItem.value.isFound ? 'found' : 'lost',
      comment: newComment.value.trim()
    };
    
    console.log('Submitting comment:', commentData);
    const response = await axios.post('/comments', commentData);
    console.log('Comment submitted:', response.data);
    
    // Add the new comment to the list
    const newCommentObj = {
      ...response.data,
      userName: response.data.user?.name || 'Unknown User',
      created_at: formatDate(response.data.created_at)
    };
    
    selectedItem.value.comments.push(newCommentObj);
    newComment.value = ''; // Clear the input
  } catch (error) {
    console.error('Error submitting comment:', error);
  }
};

const handleCommentSubmit = async ({ itemId, text, itemType }) => {
  try {
    // Post the new comment
    const { data } = await axios.post(`/api/comments`, {
      item_id: itemId,
      text: text,
      item_type: itemType
    });

    if (data.comment) {
      // Update the selectedItem's comments array by adding the new comment
      const newComment = {
        ...data.comment,
        userName: data.comment.user?.name || currentUserId.value?.name || 'Unknown User',
        user: {
          ...data.comment.user,
          name: data.comment.user?.name || currentUserId.value?.name || 'Unknown User'
        }
      };
      
      selectedItem.value = {
        ...selectedItem.value,
        comments: [...(selectedItem.value.comments || []), newComment]
      };
    }

    // Refresh all comments to ensure everything is in sync
    await fetchComments();
  } catch (error) {
    console.error('Error submitting comment:', error);
  }
};

const closeModal = () => {
  showCommentModal.value = false;
  selectedItem.value = null;
};

const fetchCurrentUser = async () => {
  try {
    const response = await axios.get(window.userID);
    currentUserId.value = response.data.id;
  } catch (error) {
    console.error("Error fetching user ID:", error.message);
  }
};

const fetchUsers = async () => {
  try {
    const response = await axios.get('/users');
    response.data.forEach(user => {
      users.value[user.id] = user;
    });
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};

const fetchItems = async () => {
  try {
    const lostItemsResponse = await axios.get('/lost-items');
    const foundItemsResponse = await axios.get('/found-items');

    lostItemsResponse.data.forEach(item => {
      items.value[item.id] = { item_name: item.item_name, user_id: item.user_id };
    });
    foundItemsResponse.data.forEach(item => {
      items.value[item.id] = { item_name: item.item_name, user_id: item.user_id };
    });
  } catch (error) {
    console.error('Error fetching items:', error);
  }
};

const fetchNotifications = async () => {
  try {
    const response = await axios.get('/notifications');
    console.log('Fetched notifications:', response.data);

    // Map notifications to include parsed data
    notifications.value = response.data.notifications.map(notification => {
      // Parse notification data if it's a string
      const parsedData = typeof notification.data === 'string'
        ? JSON.parse(notification.data)
        : notification.data;

      return {
        ...notification,
        data: parsedData,
        userName: parsedData.commenter_name || notification.user?.name || 'Unknown User'
      };
    });

    // Calculate unread count
    unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    console.log('Unread notifications count:', unreadCount.value);

    // Fetch comments for notifications
    await fetchComments();
  } catch (error) {
    console.error('Error fetching notifications:', error);
    notifications.value = [];
    unreadCount.value = 0;
  }
};

const fetchComments = async () => {
  try {
    for (const notification of notifications.value) {
      try {
        // Parse notification data if it's a string
        const notificationData = typeof notification.data === 'string'
          ? JSON.parse(notification.data)
          : notification.data;

        // Skip if we don't have valid item data
        if (!notificationData || !notificationData.item_id || !notificationData.item_type) {
          console.warn('Invalid notification data:', notification);
          notification.comments = [];
          continue;
        }

        const itemType = notificationData.item_type;
        const itemId = notificationData.item_id;
        
        console.log(`Fetching comments for ${itemType} item ${itemId}:`, {
          notification_id: notification.id,
          item_type: itemType,
          item_id: itemId
        });

        const response = await axios.get(`/comments/${itemType}/${itemId}`);
        console.log(`Comments for item ${itemId}:`, response.data);

        // Handle different response formats
        const comments = response.data.comments || response.data || [];
        
        // Ensure each comment has a userName
        notification.comments = comments.map(comment => ({
          ...comment,
          userName: comment.user?.name || comment.userName || 'Unknown User',
          created_at: formatDate(comment.created_at)
        }));

        // Update the notification's comment text if it exists in the comments
        const relatedComment = comments.find(c => c.id === notificationData.comment_id);
        if (relatedComment) {
          notification.data = {
            ...notificationData,
            comment_text: relatedComment.text
          };
        }
      } catch (error) {
        console.error(`Error fetching comments for notification item ${notification.id}:`, error.message);
        notification.comments = [];
      }
    }
  } catch (error) {
    console.error("Error in fetchComments:", error.message);
  }
};

const formatDate = (date) => {
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(date).toLocaleString(undefined, options);
};

const markAsRead = async (notificationId) => {
  try {
    console.log('Marking notification as read:', notificationId);
    await axios.put(`/notifications/${notificationId}/read`);
    
    // Update the notification in our local state
    const notification = notifications.value.find(n => n.id === notificationId);
    if (notification) {
      notification.read_at = new Date().toISOString();
      // Update unread count
      unreadCount.value = notifications.value.filter(n => !n.read_at).length;
      console.log('Updated unread count after marking as read:', unreadCount.value);
    }
  } catch (error) {
    console.error('Error marking notification as read:', error);
    console.error('Error details:', error.response?.data || error.message);
  }
};

// Add watcher for notifications to update unread count
watch(notifications, (newNotifications) => {
  unreadCount.value = newNotifications.filter(n => !n.read_at).length;
  console.log('Updated unread count from watcher:', unreadCount.value);
}, { deep: true });

// Fetch notifications on mount
onMounted(async () => {
  await fetchCurrentUser();
  await fetchUsers();
  await fetchItems();
  await fetchNotifications();
});
</script>

<style scoped>
button svg {
  width: 24px;
  height: 24px;
}

button:focus {
  outline: none;
}

.notification-dropdown {
  width: 16rem;
}

.notification-dropdown div {
  cursor: pointer;
}

.notification-dropdown div:hover {
  background-color: #f3f4f6;
}
</style>
