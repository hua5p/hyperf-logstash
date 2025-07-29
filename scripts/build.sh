#!/bin/bash

# 构建 Hyperf Logstash 包

echo "🚀 开始构建 Hyperf Logstash 包..."

# 检查依赖
if ! command -v composer &> /dev/null; then
    echo "❌ Composer 未安装"
    exit 1
fi

# 安装依赖
echo "📦 安装依赖..."
composer install --no-dev --optimize-autoloader

# 运行测试
echo "🧪 运行测试..."
composer test

# 检查测试结果
if [ $? -eq 0 ]; then
    echo "✅ 测试通过"
else
    echo "❌ 测试失败"
    exit 1
fi

# 创建发布包
echo "📦 创建发布包..."
composer archive --format=zip --dir=dist

echo "✅ 构建完成！"
echo "📁 发布包位置: dist/" 