#!/bin/bash

# Hyperf Logstash 包发布脚本

set -e

echo "🚀 开始发布 Hyperf Logstash 包..."

# 检查参数
if [ -z "$1" ]; then
    echo "❌ 请提供版本号"
    echo "用法: ./scripts/publish.sh <version>"
    echo "示例: ./scripts/publish.sh 1.0.0"
    exit 1
fi

VERSION=$1

echo "📦 发布版本: $VERSION"

# 检查是否在正确的分支
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo "❌ 请在 main 分支上发布"
    exit 1
fi

# 检查工作目录是否干净
if [ -n "$(git status --porcelain)" ]; then
    echo "❌ 工作目录不干净，请先提交所有更改"
    exit 1
fi

# 运行测试
echo "🧪 运行测试..."
composer test

# 构建包
echo "📦 构建包..."
composer archive --format=zip --dir=dist

# 创建标签
echo "🏷️  创建标签 v$VERSION..."
git tag -a "v$VERSION" -m "Release version $VERSION"

# 推送标签
echo "📤 推送标签..."
git push origin "v$VERSION"

# 更新 composer.json 版本
echo "📝 更新 composer.json 版本..."
sed -i '' "s/\"version\": \".*\"/\"version\": \"$VERSION\"/" composer.json

# 提交版本更新
git add composer.json
git commit -m "chore: bump version to $VERSION"
git push origin main

echo "✅ 发布完成！"
echo "📋 下一步："
echo "1. 在 GitHub 上检查 Release 是否创建成功"
echo "2. 在 Packagist 上提交包（如果还没有）"
echo "3. 验证包可以通过 composer require hua5p/hyperf-logstash 安装" 