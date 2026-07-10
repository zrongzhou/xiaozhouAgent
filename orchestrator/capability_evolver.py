"""能力进化引擎（方案第十一/十八章：流程/知识进化）。

功能：
- 记录运行历史
- 分析失败模式
- 生成优化建议
- 更新模型画像和提示模板
"""
import os
import json
import logging
import asyncio
from datetime import datetime, timezone
from typing import Optional, Dict, Any, List
from dataclasses import dataclass, field, asdict
from collections import defaultdict

logger = logging.getLogger("capability_evolver")


@dataclass
class EvolutionRecord:
    """进化记录"""
    timestamp: str
    task_id: str
    task_type: Optional[str] = None
    success: bool = True
    error: Optional[str] = None
    duration_ms: float = 0.0
    tokens_input: int = 0
    tokens_output: int = 0
    cost_usd: float = 0.0
    model_used: Optional[str] = None
    metadata: Dict[str, Any] = field(default_factory=dict)


class CapabilityEvolver:
    """能力进化引擎"""
    
    def __init__(self, data_dir: str = "/data"):
        self.data_dir = data_dir
        self.history: List[EvolutionRecord] = []
        self._patterns: Dict[str, List[EvolutionRecord]] = defaultdict(list)
        self._initialized = False
        
    def init(self):
        """初始化进化器"""
        if self._initialized:
            return
            
        # 加载历史数据
        self._load_history()
        self._initialized = True
        logger.info(f"CapabilityEvolver initialized with {len(self.history)} records")
        
    def _load_history(self):
        """加载历史进化记录"""
        history_path = os.path.join(self.data_dir, "evolution", "history.json")
        if os.path.exists(history_path):
            try:
                with open(history_path, "r", encoding="utf-8") as f:
                    records = json.load(f)
                    for record_data in records:
                        record = EvolutionRecord(**record_data)
                        self.history.append(record)
                        if record.task_type:
                            self._patterns[record.task_type].append(record)
            except Exception as e:
                logger.warning(f"Failed to load evolution history: {e}")
                
    def _save_history(self):
        """保存历史进化记录"""
        history_path = os.path.join(self.data_dir, "evolution", "history.json")
        os.makedirs(os.path.dirname(history_path), exist_ok=True)
        
        with open(history_path, "w", encoding="utf-8") as f:
            json.dump(
                [asdict(record) for record in self.history],
                f,
                ensure_ascii=False,
                indent=2,
            )
            
    def ingest(self, report: Dict[str, Any]) -> None:
        """摄入运行报告"""
        self.init()
        
        record = EvolutionRecord(
            timestamp=_now(),
            task_id=report.get("task_id", "unknown"),
            task_type=report.get("task_type"),
            success=report.get("ok", report.get("success", True)),
            error=report.get("error"),
            duration_ms=report.get("duration_ms", 0.0),
            tokens_input=report.get("tokens_input", 0),
            tokens_output=report.get("tokens_output", 0),
            cost_usd=report.get("cost_usd", 0.0),
            model_used=report.get("model_used"),
            metadata=report.get("metadata", {}),
        )
        
        self.history.append(record)
        if record.task_type:
            self._patterns[record.task_type].append(record)
            
        # 保存到磁盘
        self._save_history()
        
        logger.info(f"Evolution ingest: task={record.task_id}, success={record.success}")
        
    def get_patterns(self) -> Dict[str, Dict[str, Any]]:
        """获取失败模式分析"""
        patterns = {}
        
        for task_type, records in self._patterns.items():
            total = len(records)
            failures = [r for r in records if not r.success]
            
            if total == 0:
                continue
                
            # 错误分类
            error_groups = defaultdict(list)
            for failure in failures:
                error = failure.error or "unknown"
                # 简单错误分类
                if "timeout" in error.lower():
                    error_groups["timeout"].append(failure)
                elif "rate limit" in error.lower():
                    error_groups["rate_limit"].append(failure)
                elif "invalid" in error.lower():
                    error_groups["invalid_input"].append(failure)
                else:
                    error_groups["other"].append(failure)
                    
            patterns[task_type] = {
                "total": total,
                "success": total - len(failures),
                "failure_rate": len(failures) / total * 100 if total > 0 else 0,
                "avg_duration_ms": sum(r.duration_ms for r in records) / total if total > 0 else 0,
                "error_distribution": {
                    error_type: len(errors)
                    for error_type, errors in error_groups.items()
                },
            }
            
        return patterns
    
    def suggest(self) -> Dict[str, Any]:
        """生成优化建议"""
        self.init()
        
        suggestions = {
            "timestamp": _now(),
            "total_samples": len(self.history),
            "overall_success_rate": (
                sum(1 for r in self.history if r.success) / len(self.history) * 100
                if self.history else 0
            ),
            "patterns": self.get_patterns(),
            "recommendations": [],
        }
        
        # 基于模式生成建议
        for task_type, pattern in suggestions["patterns"].items():
            if pattern["failure_rate"] > 30:
                suggestions["recommendations"].append({
                    "task_type": task_type,
                    "priority": "high",
                    "issue": f"高失败率 ({pattern['failure_rate']:.1f}%)",
                    "suggestion": "考虑更换模型或优化提示词",
                })
                
            # 检查特定错误类型
            error_dist = pattern["error_distribution"]
            if error_dist.get("timeout", 0) > 5:
                suggestions["recommendations"].append({
                    "task_type": task_type,
                    "priority": "medium",
                    "issue": "频繁超时",
                    "suggestion": "增加超时时间或优化请求大小",
                })
                
        return suggestions
    
    def export_knowledge(self) -> Dict[str, Any]:
        """导出进化知识"""
        return {
            "version": "1.0",
            "exported_at": _now(),
            "stats": {
                "total_records": len(self.history),
                "success_rate": self.suggest()["overall_success_rate"],
            },
            "patterns": self.get_patterns(),
            "recommendations": self.suggest()["recommendations"],
        }
    
    def reset(self):
        """重置进化记录"""
        self.history = []
        self._patterns = defaultdict(list)
        self._save_history()
        logger.info("Evolution history reset")


def _now() -> str:
    """获取当前时间的 ISO 格式字符串"""
    return datetime.now(timezone.utc).isoformat()
