# POE1 Build Recommendations

Budget-friendly Path of Exile 1 builds backed by community Path of Building data.

## League Starters (low cost)

| Build | Class | Cost | Strength |
|-------|-------|------|----------|
| [Poisonous Concoction Pathfinder](01-poisonous-concoction-pathfinder.md) | Ranger → Pathfinder | Very low | Fast clear, strong boss DPS, great league starter |
| [Toxic Rain Pathfinder](02-toxic-rain-pathfinder.md) | Ranger → Pathfinder | Low | Safe ranged mapping, minimal gear requirements |
| [Bone Shatter Juggernaut](03-bone-shatter-juggernaut.md) | Marauder → Juggernaut | Very low | Tanky melee, simple mechanics, HC-viable |

## Endgame (500M+ PoB DPS, cheap relative to mirror builds)

| Build | Class | Budget | PoB DPS | Strength |
|-------|-------|--------|---------|----------|
| [Minion Pact Spark Gladiator](04-minion-pact-spark-gladiator.md) | Duelist → Gladiator | ~30–80 div | ~403M → ~762M | Best DPS per divine; crit Spark |
| [Animate Guardian of Smiting](05-animate-guardian-of-smiting.md) | Marauder → Guardian | ~50–120 div | ~514M | Minion boss melter, tanky |
| [Detonate Dead Chain Reaction](06-detonate-dead-chain-reaction-necro.md) | Witch → Necromancer | ~40–150 div | ~399M → 900M+ | Delve / uber boss ramping DPS |

## Data-Driven (from Path of Building game files)

Built by reading skill stats, unique mods, and scaling tags in `repos/path-of-building/src/Data/` — no community PoB imports.

| Build | Class | Skill (PoB variant ID) | Data source |
|-------|-------|------------------------|-------------|
| [Cold Snap of Power](07-cold-snap-of-power-elementalist.md) | Witch → Elementalist | `ColdSnapAltX` | Power-charge cold spell; 3s CD bypass |
| [RF of Arcane Devotion](08-righteous-fire-arcane-devotion-hierophant.md) | Templar → Hierophant | `RighteousFireAltX` | Mana-scaling fire DoT (4200%/min) |
| [Lightning Strike of Arcing](09-lightning-strike-of-arcing-raider.md) | Ranger → Raider | `LightningStrikeAltX` | Chaining attack; +10% more per chain |

> **Naming note:** *Arcing* is the official transfigured gem suffix (lightning arcs/chains between targets). It is not “blocked” and is unrelated to block chance.

Import builds into [Path of Building Community](https://pathofbuilding.community). Enable shock, frenzy, and power charges in PoB config where noted to match published DPS figures.
