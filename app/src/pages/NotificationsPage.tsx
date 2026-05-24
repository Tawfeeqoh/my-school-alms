import { useState } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import {
  Bell,
  FileText,
  Calendar,
  AlertTriangle,
  CheckCircle2,
  MessageSquare,
  Star,
  Trash2,
  CheckCheck,
} from 'lucide-react'

interface Notification {
  id: number
  type: 'assignment' | 'announcement' | 'grade' | 'deadline' | 'message' | 'system'
  title: string
  description: string
  time: string
  read: boolean
}

const initialNotifications: Notification[] = [
  { id: 1, type: 'assignment', title: 'New Assignment Posted', description: 'CS 201: Programming Assignment #4 has been posted. Due May 28.', time: '10 minutes ago', read: false },
  { id: 2, type: 'announcement', title: 'Exam Timetable Released', description: 'Second semester examination timetable is now available for viewing.', time: '1 hour ago', read: false },
  { id: 3, type: 'grade', title: 'Grade Published', description: 'Your BIO 201 practical assessment grade has been published. You scored 88%.', time: '2 hours ago', read: false },
  { id: 4, type: 'deadline', title: 'Deadline Reminder', description: 'SLT 301 Lab Report is due in 24 hours. Don\'t forget to submit!', time: '3 hours ago', read: false },
  { id: 5, type: 'message', title: 'Message from Dr. Adeyemi', description: 'Please see me in my office tomorrow regarding your project proposal.', time: '5 hours ago', read: true },
  { id: 6, type: 'system', title: 'System Maintenance', description: 'ALMS will undergo scheduled maintenance on Saturday, May 24 from 2-4 AM.', time: '8 hours ago', read: true },
  { id: 7, type: 'announcement', title: 'Library Clearance Extended', description: 'The deadline for library clearance has been extended to June 15, 2026.', time: '1 day ago', read: true },
  { id: 8, type: 'grade', title: 'CA Results Published', description: 'STAT 101 Continuous Assessment results are now available.', time: '1 day ago', read: true },
  { id: 9, type: 'assignment', title: 'Assignment Graded', description: 'AHP 101: Your Veterinary Practical Report has been graded.', time: '2 days ago', read: true },
  { id: 10, type: 'system', title: 'Welcome to ALMS', description: 'Thank you for joining ALMS. Explore your courses and start learning!', time: '1 week ago', read: true },
]

const typeConfig = {
  assignment: { icon: FileText, color: '#2563EB', bg: 'rgba(37, 99, 235, 0.08)' },
  announcement: { icon: Bell, color: '#D10000', bg: 'rgba(209, 0, 0, 0.08)' },
  grade: { icon: Star, color: '#059669', bg: 'rgba(5, 150, 105, 0.08)' },
  deadline: { icon: AlertTriangle, color: '#D97706', bg: 'rgba(217, 119, 6, 0.08)' },
  message: { icon: MessageSquare, color: '#7C3AED', bg: 'rgba(124, 58, 237, 0.08)' },
  system: { icon: Calendar, color: '#6B7280', bg: 'rgba(107, 114, 128, 0.08)' },
}

export default function NotificationsPage() {
  const [notifications, setNotifications] = useState<Notification[]>(initialNotifications)
  const [filter, setFilter] = useState<'all' | 'unread'>('all')

  const filtered = filter === 'unread' ? notifications.filter((n) => !n.read) : notifications
  const unreadCount = notifications.filter((n) => !n.read).length

  const markAsRead = (id: number) => {
    setNotifications((prev) => prev.map((n) => (n.id === id ? { ...n, read: true } : n)))
  }

  const markAllAsRead = () => {
    setNotifications((prev) => prev.map((n) => ({ ...n, read: true })))
  }

  const deleteNotification = (id: number) => {
    setNotifications((prev) => prev.filter((n) => n.id !== id))
  }

  return (
    <DashboardLayout>
      <div className="max-w-3xl mx-auto">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <div>
            <h2 className="text-2xl font-bold text-black flex items-center gap-2">
              Notifications
              {unreadCount > 0 && (
                <span className="text-sm font-medium bg-[#D10000] text-white px-2 py-0.5 rounded-full">
                  {unreadCount} new
                </span>
              )}
            </h2>
            <p className="text-sm text-gray-500 mt-0.5">Stay updated on your academic activities</p>
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={() => setFilter(filter === 'all' ? 'unread' : 'all')}
              className={`px-4 py-2 rounded-xl text-sm font-medium transition-all ${
                filter === 'unread'
                  ? 'bg-[#D10000] text-white'
                  : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'
              }`}
            >
              {filter === 'all' ? 'Show Unread' : 'Show All'}
            </button>
            {unreadCount > 0 && (
              <button
                onClick={markAllAsRead}
                className="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 bg-white hover:bg-gray-100 border border-gray-200 transition-all"
              >
                <CheckCheck className="w-4 h-4" />
                Mark all read
              </button>
            )}
          </div>
        </div>

        {/* Notifications List */}
        <div className="space-y-2">
          {filtered.map((notification) => {
            const config = typeConfig[notification.type]
            const Icon = config.icon
            return (
              <div
                key={notification.id}
                onClick={() => markAsRead(notification.id)}
                className={`group flex items-start gap-4 p-4 rounded-2xl transition-all cursor-pointer ${
                  notification.read
                    ? 'bg-white hover:bg-gray-50'
                    : 'bg-white border-l-4 shadow-sm hover:shadow-md'
                }`}
                style={!notification.read ? { borderLeftColor: config.color } : undefined}
              >
                <div
                  className="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
                  style={{ backgroundColor: config.bg }}
                >
                  <Icon className="w-5 h-5" style={{ color: config.color }} />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-start justify-between gap-2">
                    <div>
                      <h4 className={`text-sm font-semibold ${notification.read ? 'text-gray-700' : 'text-black'}`}>
                        {notification.title}
                      </h4>
                      <p className="text-sm text-gray-500 mt-0.5 leading-relaxed">
                        {notification.description}
                      </p>
                      <span className="text-[10px] text-gray-400 mt-1.5 block font-mono-data">
                        {notification.time}
                      </span>
                    </div>
                    {!notification.read && (
                      <div className="w-2 h-2 rounded-full flex-shrink-0 mt-1.5" style={{ backgroundColor: config.color }} />
                    )}
                  </div>
                </div>
                <button
                  onClick={(e) => {
                    e.stopPropagation()
                    deleteNotification(notification.id)
                  }}
                  className="opacity-0 group-hover:opacity-100 p-2 rounded-lg hover:bg-red-50 text-gray-400 hover:text-[#D10000] transition-all"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            )
          })}

          {filtered.length === 0 && (
            <div className="text-center py-16 bg-white rounded-3xl">
              <CheckCircle2 className="w-12 h-12 text-green-400 mx-auto mb-4" />
              <h3 className="text-lg font-semibold text-gray-500">
                {filter === 'unread' ? 'All caught up!' : 'No notifications'}
              </h3>
              <p className="text-sm text-gray-400 mt-1">
                {filter === 'unread' ? 'You have no unread notifications.' : 'Your notification list is empty.'}
              </p>
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  )
}
