import { useState } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import {
  User,
  Bell,
  Shield,
  Palette,
  ChevronRight,
  ToggleLeft,
  ToggleRight,
} from 'lucide-react'

interface SettingSection {
  title: string
  icon: React.ElementType
  items: {
    label: string
    description?: string
    type: 'toggle' | 'link' | 'select'
    value?: boolean
    options?: string[]
  }[]
}

export default function SettingsPage() {
  const [settings, setSettings] = useState({
    emailNotifications: true,
    pushNotifications: true,
    deadlineReminders: true,
    gradeAlerts: true,
    announcementEmails: false,
    darkMode: false,
    compactView: false,
    autoSave: true,
    twoFactor: false,
    sessionTimeout: '30 minutes',
  })

  const toggleSetting = (key: string) => {
    setSettings((prev) => ({ ...prev, [key]: !prev[key as keyof typeof prev] }))
  }

  const sections: SettingSection[] = [
    {
      title: 'Notifications',
      icon: Bell,
      items: [
        { label: 'Email Notifications', description: 'Receive updates via email', type: 'toggle', value: settings.emailNotifications },
        { label: 'Push Notifications', description: 'Browser push notifications', type: 'toggle', value: settings.pushNotifications },
        { label: 'Deadline Reminders', description: 'Get reminded 24h before deadlines', type: 'toggle', value: settings.deadlineReminders },
        { label: 'Grade Alerts', description: 'Notification when grades are published', type: 'toggle', value: settings.gradeAlerts },
        { label: 'Announcement Emails', description: 'Weekly digest of announcements', type: 'toggle', value: settings.announcementEmails },
      ],
    },
    {
      title: 'Appearance',
      icon: Palette,
      items: [
        { label: 'Dark Mode', description: 'Switch to dark theme', type: 'toggle', value: settings.darkMode },
        { label: 'Compact View', description: 'Reduce spacing for more content', type: 'toggle', value: settings.compactView },
        { label: 'Language', type: 'select', options: ['English', 'Yoruba', 'Hausa', 'Igbo'] },
      ],
    },
    {
      title: 'Security',
      icon: Shield,
      items: [
        { label: 'Two-Factor Authentication', description: 'Add extra layer of security', type: 'toggle', value: settings.twoFactor },
        { label: 'Session Timeout', type: 'select', options: ['15 minutes', '30 minutes', '1 hour', 'Never'] },
        { label: 'Change Password', type: 'link' },
        { label: 'Login History', type: 'link' },
      ],
    },
    {
      title: 'Account',
      icon: User,
      items: [
        { label: 'Connected Devices', description: 'Manage active sessions', type: 'link' },
        { label: 'Data Export', description: 'Download your data', type: 'link' },
        { label: 'Delete Account', type: 'link' },
      ],
    },
  ]

  return (
    <DashboardLayout>
      <div className="max-w-3xl mx-auto">
        {/* Header */}
        <div className="mb-8">
          <h2 className="text-2xl font-bold text-black">Settings</h2>
          <p className="text-sm text-gray-500 mt-0.5">Manage your account preferences</p>
        </div>

        {/* Settings Sections */}
        <div className="space-y-6">
          {sections.map((section, sectionIndex) => (
            <div key={sectionIndex} className="bg-white rounded-3xl shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div className="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                  <section.icon className="w-4 h-4 text-gray-500" />
                </div>
                <h3 className="text-lg font-semibold text-black">{section.title}</h3>
              </div>
              <div className="divide-y divide-gray-50">
                {section.items.map((item, itemIndex) => (
                  <div
                    key={itemIndex}
                    className="px-6 py-4 flex items-center justify-between hover:bg-gray-50/50 transition-colors"
                  >
                    <div className="flex-1">
                      <p className="text-sm font-medium text-black">{item.label}</p>
                      {item.description && (
                        <p className="text-xs text-gray-500 mt-0.5">{item.description}</p>
                      )}
                    </div>

                    {item.type === 'toggle' && (
                      <button
                        onClick={() => {
                          const keyMap: Record<string, string> = {
                            'Email Notifications': 'emailNotifications',
                            'Push Notifications': 'pushNotifications',
                            'Deadline Reminders': 'deadlineReminders',
                            'Grade Alerts': 'gradeAlerts',
                            'Announcement Emails': 'announcementEmails',
                            'Dark Mode': 'darkMode',
                            'Compact View': 'compactView',
                            'Two-Factor Authentication': 'twoFactor',
                          }
                          toggleSetting(keyMap[item.label] || '')
                        }}
                        className="ml-4 flex-shrink-0"
                      >
                        {item.value ? (
                          <ToggleRight className="w-10 h-6 text-[#D10000]" />
                        ) : (
                          <ToggleLeft className="w-10 h-6 text-gray-300" />
                        )}
                      </button>
                    )}

                    {item.type === 'select' && (
                      <select className="ml-4 text-sm bg-gray-100 rounded-lg px-3 py-1.5 border-none outline-none cursor-pointer">
                        {item.options?.map((opt) => (
                          <option key={opt} value={opt}>{opt}</option>
                        ))}
                      </select>
                    )}

                    {item.type === 'link' && (
                      <ChevronRight className="w-4 h-4 text-gray-400" />
                    )}
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>

        {/* App Info */}
        <div className="text-center mt-8 mb-4">
          <p className="text-xs text-gray-400">
            ALMS v2.0.1 &middot; FCAHPT Ibadan &middot; Built with empathy for students
          </p>
        </div>
      </div>
    </DashboardLayout>
  )
}
