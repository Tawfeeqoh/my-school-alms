import { useState, useRef, useEffect } from 'react'
import DashboardLayout from '../components/DashboardLayout'
import { Send, Sparkles, BookOpen, Lightbulb, Calculator, FileText, Bot, User } from 'lucide-react'

interface Message {
  id: number
  role: 'user' | 'assistant'
  content: string
  timestamp: Date
}

const quickPrompts = [
  { icon: BookOpen, text: 'Explain photosynthesis in simple terms' },
  { icon: Calculator, text: 'Help me solve this statistics problem' },
  { icon: FileText, text: 'Summarize Chapter 5 of Organic Chemistry' },
  { icon: Lightbulb, text: 'Study tips for upcoming exams' },
]

const sampleResponses: Record<string, string> = {
  'explain photosynthesis in simple terms':
    'Photosynthesis is the process by which plants use sunlight, water, and carbon dioxide to create their own food (glucose) and release oxygen. Think of it as nature\'s way of turning light energy into chemical energy. The process happens in the chloroplasts of plant cells, specifically using chlorophyll (the green pigment) to capture light energy.',
  'help me solve this statistics problem':
    'I\'d be happy to help! To solve a statistics problem, we typically follow these steps:\n\n1. **Identify what you\'re being asked to find** (mean, median, standard deviation, etc.)\n2. **Organize your data** - arrange numbers in order if needed\n3. **Choose the right formula** - for mean: sum all values and divide by count\n4. **Calculate carefully** - double-check your arithmetic\n5. **Interpret the result** - what does the answer tell you about the data?\n\nShare your specific problem and I\'ll walk you through it step by step!',
  'summarize chapter 5 of organic chemistry':
    'Chapter 5 of Organic Chemistry typically covers **Stereochemistry**. Here\'s a summary:\n\n**Key Concepts:**\n- **Chirality**: Molecules that are non-superimposable mirror images\n- **Enantiomers**: Pairs of chiral molecules (like left and right hands)\n- **Diastereomers**: Stereoisomers that are NOT mirror images\n- **Optical Activity**: Ability to rotate plane-polarized light\n- **R/S Configuration**: System for naming stereocenters (Cahn-Ingold-Prelog rules)\n\n**Important Rules:**\n- A carbon with 4 different groups attached is a stereocenter\n- Maximum number of stereoisomers = 2^n (where n = number of stereocenters)\n\nWould you like me to go deeper into any specific topic?',
  'study tips for upcoming exams':
    'Here are evidence-based study strategies that work:\n\n**1. Spaced Repetition**\nDon\'t cram! Study in spaced intervals (1 day, 3 days, 1 week) for better long-term retention.\n\n**2. Active Recall**\nTest yourself instead of re-reading. Use flashcards or practice questions.\n\n**3. Pomodoro Technique**\nStudy for 25 minutes, break for 5. After 4 cycles, take a 15-30 minute break.\n\n**4. Feynman Technique**\nExplain concepts in your own words as if teaching a child. If you get stuck, review and simplify.\n\n**5. Sleep & Nutrition**\nYour brain consolidates memories during sleep. Aim for 7-8 hours and stay hydrated!\n\n**6. Practice Past Questions**\nFCAHPT often repeats question patterns. Practice at least 5 years of past questions.',
  default:
    'I\'m here to help with your academic journey at FCAHPT Ibadan! I can assist with:\n\n- Explaining complex concepts in simpler terms\n- Helping solve problems step by step\n- Summarizing course materials\n- Providing study strategies and exam tips\n- Answering questions about your courses\n\nWhat would you like to learn about today?',
}

export default function AIAssistantPage() {
  const [messages, setMessages] = useState<Message[]>([
    {
      id: 0,
      role: 'assistant',
      content: "Hello! I'm your ALMS AI Study Assistant. I'm here to help you understand concepts, solve problems, and succeed in your studies at FCAHPT Ibadan. What can I help you with today?",
      timestamp: new Date(),
    },
  ])
  const [input, setInput] = useState('')
  const [isTyping, setIsTyping] = useState(false)
  const messagesEndRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages])

  const getResponse = (query: string): string => {
    const normalized = query.toLowerCase().trim()
    for (const [key, value] of Object.entries(sampleResponses)) {
      if (normalized.includes(key.replace(/^(explain|help me|summarize)/, '').trim())) {
        return value
      }
    }
    return sampleResponses.default
  }

  const handleSend = (text?: string) => {
    const content = text || input.trim()
    if (!content) return

    const userMsg: Message = {
      id: messages.length,
      role: 'user',
      content,
      timestamp: new Date(),
    }

    setMessages((prev) => [...prev, userMsg])
    setInput('')
    setIsTyping(true)

    setTimeout(() => {
      const response = getResponse(content)
      const assistantMsg: Message = {
        id: messages.length + 1,
        role: 'assistant',
        content: response,
        timestamp: new Date(),
      }
      setMessages((prev) => [...prev, assistantMsg])
      setIsTyping(false)
    }, 1000 + Math.random() * 1000)
  }

  return (
    <DashboardLayout>
      <div className="max-w-4xl mx-auto h-[calc(100vh-8rem)] flex flex-col">
        {/* Header */}
        <div className="flex items-center gap-4 mb-4 pb-4 border-b border-gray-200">
          <div className="w-10 h-10 rounded-xl flex items-center justify-center" style={{ backgroundColor: '#D10000' }}>
            <Sparkles className="w-5 h-5 text-white" />
          </div>
          <div>
            <h2 className="text-lg font-bold text-black flex items-center gap-2">
              AI Study Assistant
              <span className="text-[10px] font-mono-data bg-green-100 text-green-700 px-2 py-0.5 rounded-full">ONLINE</span>
            </h2>
            <p className="text-xs text-gray-500">Powered by advanced academic AI</p>
          </div>
        </div>

        {/* Messages */}
        <div className="flex-1 overflow-y-auto space-y-4 pr-2 scrollbar-hide">
          {messages.map((msg) => (
            <div
              key={msg.id}
              className={`flex gap-3 ${msg.role === 'user' ? 'flex-row-reverse' : ''}`}
            >
              <div
                className={`w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 ${
                  msg.role === 'user' ? 'bg-gray-200' : 'bg-[#D10000]'
                }`}
              >
                {msg.role === 'user' ? (
                  <User className="w-4 h-4 text-gray-600" />
                ) : (
                  <Bot className="w-4 h-4 text-white" />
                )}
              </div>
              <div
                className={`max-w-[80%] rounded-2xl px-4 py-3 ${
                  msg.role === 'user'
                    ? 'bg-[#D10000] text-white rounded-br-md'
                    : 'bg-white shadow-sm rounded-bl-md'
                }`}
              >
                <p className={`text-sm whitespace-pre-line leading-relaxed ${msg.role === 'user' ? 'text-white' : 'text-gray-800'}`}>
                  {msg.content}
                </p>
                <span
                  className={`text-[10px] mt-1 block ${
                    msg.role === 'user' ? 'text-white/60' : 'text-gray-400'
                  }`}
                >
                  {msg.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </span>
              </div>
            </div>
          ))}

          {isTyping && (
            <div className="flex gap-3">
              <div className="w-8 h-8 rounded-full bg-[#D10000] flex items-center justify-center flex-shrink-0">
                <Bot className="w-4 h-4 text-white" />
              </div>
              <div className="bg-white shadow-sm rounded-2xl rounded-bl-md px-4 py-3">
                <div className="flex gap-1.5">
                  <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                  <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                  <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                </div>
              </div>
            </div>
          )}

          <div ref={messagesEndRef} />
        </div>

        {/* Quick Prompts */}
        {messages.length === 1 && (
          <div className="flex gap-2 overflow-x-auto scrollbar-hide pb-3 mt-2">
            {quickPrompts.map((prompt, index) => (
              <button
                key={index}
                onClick={() => handleSend(prompt.text)}
                className="flex items-center gap-2 px-4 py-2.5 bg-white rounded-xl border border-gray-200 hover:border-[#D10000]/30 hover:bg-red-50 transition-all whitespace-nowrap text-sm text-gray-700 hover:text-[#D10000]"
              >
                <prompt.icon className="w-4 h-4 flex-shrink-0" />
                {prompt.text}
              </button>
            ))}
          </div>
        )}

        {/* Input */}
        <div className="mt-4 pt-4 border-t border-gray-200">
          <div className="flex gap-3">
            <input
              type="text"
              value={input}
              onChange={(e) => setInput(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && handleSend()}
              placeholder="Ask me anything about your studies..."
              className="input-field flex-1"
            />
            <button
              onClick={() => handleSend()}
              disabled={!input.trim() || isTyping}
              className="btn-primary px-5 disabled:opacity-50"
            >
              <Send className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </DashboardLayout>
  )
}
