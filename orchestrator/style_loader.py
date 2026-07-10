"""风格加载器（方案第十九章：风格引导）。

功能：
- 加载 /config/style-guide.yaml
- 提供风格上下文给每次构建
- 支持风格预设查询
"""
import os
import logging
from typing import Optional, Dict, Any, List

try:
    import yaml
except ImportError:
    yaml = None

logger = logging.getLogger("style_loader")


class StylePreset:
    """风格预设"""
    
    def __init__(self, slug: str, data: Dict[str, Any]):
        self.slug = slug
        self.name = data.get("name", slug)
        self.description = data.get("description", "")
        self.colors = data.get("colors", {})
        self.typography = data.get("typography", {})
        self.components = data.get("components", {})
        self.layout = data.get("layout", {})
        

class StyleLoader:
    """风格加载器"""
    
    def __init__(self, config_dir: str):
        self.config_dir = config_dir
        self.name: Optional[str] = None
        self.data: Dict[str, Any] = {}
        self.presets: Dict[str, StylePreset] = {}
        self._load()
        
    def _load(self) -> None:
        """加载风格配置"""
        path = os.path.join(self.config_dir, "style-guide.yaml")
        if not os.path.exists(path) or yaml is None:
            logger.warning("style-guide.yaml 缺失或 pyyaml 未装，使用默认风格")
            self._load_defaults()
            return
            
        with open(path, "r", encoding="utf-8") as fh:
            self.data = yaml.safe_load(fh) or {}
            
        self.name = self.data.get("name", "default")
        
        # 加载预设
        presets_data = self.data.get("presets", {})
        for slug, preset_data in presets_data.items():
            self.presets[slug] = StylePreset(slug, preset_data)
            
        logger.info(f"Loaded style guide: {self.name}, {len(self.presets)} presets")
        
    def _load_defaults(self):
        """加载默认风格"""
        self.name = "modern"
        self.data = {
            "name": "modern",
            "description": "现代简约风格",
            "colors": {
                "primary": "#6366F1",
                "secondary": "#8B5CF6",
                "accent": "#10B981",
                "background": "#0F172A",
                "text": "#F8FAFC",
            },
            "typography": {
                "font_family": "Inter, system-ui, sans-serif",
                "heading_size": "2rem",
                "body_size": "1rem",
                "line_height": 1.6,
            },
            "components": {
                "buttons": {"variant": "gradient"},
                "cards": {"variant": "glass"},
                "inputs": {"variant": "filled"},
            },
            "presets": {
                "modern": {
                    "name": "现代简约",
                    "description": "简洁现代的设计风格",
                    "colors": {"primary": "#6366F1"},
                },
                "luxury": {
                    "name": "高端奢华",
                    "description": "高端奢华的设计风格",
                    "colors": {"primary": "#F59E0B"},
                },
                "tech": {
                    "name": "科技感",
                    "description": "科技感强的设计风格",
                    "colors": {"primary": "#06B6D4"},
                },
            },
        }
        
    def get_preset(self, slug: str) -> Optional[StylePreset]:
        """获取风格预设"""
        return self.presets.get(slug)
        
    def get_preset_context(self, slug: str) -> Dict[str, Any]:
        """获取预设的上下文数据，用于注入到 LLM 提示中"""
        preset = self.get_preset(slug)
        if not preset:
            preset = self.get_preset("modern")  # 默认
            
        return {
            "name": preset.name,
            "description": preset.description,
            "colors": preset.colors,
            "typography": preset.typography,
            "components": preset.components,
            "layout": preset.layout,
        }
    
    def build_prompt_context(self, preset_slug: Optional[str] = None) -> str:
        """构建风格提示上下文"""
        context = self.get_preset_context(preset_slug)
        
        return f"""
设计风格: {context['name']}
风格描述: {context['description']}

颜色方案:
- 主色: {context['colors'].get('primary', '#6366F1')}
- 次色: {context['colors'].get('secondary', '#8B5CF6')}
- 强调色: {context['colors'].get('accent', '#10B981')}

排版:
- 字体: {context['typography'].get('font_family', 'Inter')}
- 标题大小: {context['typography'].get('heading_size', '2rem')}
- 正文大小: {context['typography'].get('body_size', '1rem')}

组件风格:
- 按钮: {context['components'].get('buttons', {}).get('variant', 'gradient')}
- 卡片: {context['components'].get('cards', {}).get('variant', 'glass')}
- 输入框: {context['components'].get('inputs', {}).get('variant', 'filled')}
"""
    
    def list_presets(self) -> List[Dict[str, str]]:
        """列出所有可用预设"""
        return [
            {
                "slug": slug,
                "name": preset.name,
                "description": preset.description,
            }
            for slug, preset in self.presets.items()
        ]
