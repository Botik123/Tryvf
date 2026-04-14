/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

export default function App() {
  return (
    <div className="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-8 text-center">
      <div className="bg-white p-8 rounded-xl shadow-lg max-w-2xl">
        <h1 className="text-3xl font-bold text-green-700 mb-4">Проект на Чистом PHP Готов!</h1>
        <p className="text-gray-600 mb-6">
          Я перенес всю верстку в PHP, реализовал админ-панель, REST API и логику сертификатов согласно ТЗ.
        </p>
        <div className="text-left bg-gray-50 p-4 rounded border mb-6">
          <h2 className="font-semibold mb-2">Что было сделано:</h2>
          <ul className="list-disc list-inside space-y-1 text-sm text-gray-700">
            <li>Авторизация и регистрация (bcrypt)</li>
            <li>Админ-панель: управление курсами и уроками</li>
            <li>Валидация форм и обработка изображений (миниатюры 300x300)</li>
            <li>REST API для студентов (Bearer Token)</li>
            <li>Система заказов и Webhook оплаты</li>
            <li>Генерация сертификатов с уникальными номерами</li>
          </ul>
        </div>
        <p className="text-sm text-amber-600 font-medium">
          Примечание: Для работы приложения требуется PHP 8.x и MySQL. 
          Все файлы находятся в корне проекта.
        </p>
      </div>
    </div>
  );
}
